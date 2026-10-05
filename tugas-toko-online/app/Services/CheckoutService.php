<?php
namespace App\Services;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
class CheckoutService
{
    public function checkout(User $user, string $address, string $token): string
    {
        return DB::transaction(function() use($user,$address,$token) {
            // Every cart mutation locks its owner first. This serializes concurrent
            // checkout requests even when they arrive through different sessions.
            DB::table('users')->where('id_user',$user->id_user)->lockForUpdate()->first();
            $existing = DB::table('orders')->where('checkout_token',$token)->where('id_user',$user->id_user)->first();
            if ($existing) return $existing->id_order;
            $cart = DB::table('cart_items')->where('id_user',$user->id_user)->orderBy('id_barang')->lockForUpdate()->get();
            if ($cart->isEmpty()) throw ValidationException::withMessages(['cart'=>'Keranjang masih kosong.']);
            $lines=[]; $totalCents=0;
            foreach($cart as $item) {
                $p=DB::table('products')->where('id_barang',$item->id_barang)->lockForUpdate()->first();
                if (!$p || $item->jumlah < 1 || $item->jumlah > $p->stok) {
                    throw ValidationException::withMessages(['cart'=>'Stok berubah atau tidak cukup. Periksa kembali keranjang Anda.']);
                }
                // Exact integer arithmetic; browser prices and totals are ignored.
                $cents=(int)str_replace('.','',number_format((float)$p->harga,2,'.',''));
                $totalCents += $cents*$item->jumlah;
                if ($totalCents > 999999999999) throw ValidationException::withMessages(['cart'=>'Total pesanan melebihi batas.']);
                $lines[]=['id_barang'=>$p->id_barang,'harga_satuan'=>$p->harga,'jumlah_beli'=>$item->jumlah];
            }
            $id='ORD'.strtoupper(Str::random(12));
            DB::table('orders')->insert(['id_order'=>$id,'id_user'=>$user->id_user,'tanggal_order'=>now(),
                'total_harga'=>sprintf('%d.%02d',intdiv($totalCents,100),$totalCents%100),'alamat_pengiriman'=>$address,'checkout_token'=>$token]);
            foreach($lines as $line) {
                DB::table('order_details')->insert(['id_order'=>$id]+$line);
                DB::table('products')->where('id_barang',$line['id_barang'])->decrement('stok',$line['jumlah_beli']);
            }
            DB::table('cart_items')->where('id_user',$user->id_user)->delete();
            return $id;
        },3);
    }
}
