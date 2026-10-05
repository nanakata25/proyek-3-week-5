<?php
namespace App\Http\Controllers;
use App\Models\Product;
use App\Services\CheckoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
class ShopController extends Controller
{
    public function products() { return view('products',['products'=>Product::orderBy('id_barang')->get()]); }
    public function cart(Request $r) {
        if (!$r->session()->has('checkout_token')) $r->session()->put('checkout_token',(string)Str::uuid());
        $items=DB::table('cart_items')->join('products','products.id_barang','=','cart_items.id_barang')->where('id_user',$r->user()->id_user)->orderBy('products.id_barang')->get();
        return view('cart',['items'=>$items,'total'=>$items->sum(fn($i)=>(float)$i->harga*$i->jumlah)]);
    }
    private function mutate(Request $r, string $id, bool $add) {
        $quantity=$add ? 1 : (int)$r->validate(['jumlah'=>'required|integer|min:0|max:100000'])['jumlah'];
        DB::transaction(function() use($r,$id,$add,$quantity) {
            DB::table('users')->where('id_user',$r->user()->id_user)->lockForUpdate()->first();
            $p=Product::whereKey($id)->lockForUpdate()->firstOrFail();
            $query=DB::table('cart_items')->where('id_user',$r->user()->id_user)->where('id_barang',$id);
            $n=$add ? (($query->first()->jumlah ?? 0)+1) : $quantity;
            if($n>$p->stok) throw ValidationException::withMessages(['jumlah'=>'Jumlah tidak boleh melebihi stok yang tersedia.']);
            if($n===0) { $query->delete(); return; }
            DB::table('cart_items')->updateOrInsert(['id_user'=>$r->user()->id_user,'id_barang'=>$id],['jumlah'=>$n]);
        },3);
        return back()->with('success','Keranjang diperbarui.');
    }
    public function add(Request $r,string $id) { return $this->mutate($r,$id,true); }
    public function update(Request $r,string $id) { return $this->mutate($r,$id,false); }
    public function remove(Request $r,string $id) {
        DB::transaction(function()use($r,$id){
            DB::table('users')->where('id_user',$r->user()->id_user)->lockForUpdate()->first();
            DB::table('cart_items')->where('id_user',$r->user()->id_user)->where('id_barang',$id)->delete();
        });
        return back()->with('success','Barang dihapus dari keranjang.');
    }
    public function checkout(Request $r,CheckoutService $service) {
        $data=$r->validate(['alamat_pengiriman'=>'required|string|min:10|max:1000','checkout_token'=>'required|uuid']);
        // Idempotent replay is only accepted for an already-created order of this user.
        $old=DB::table('orders')->where('checkout_token',$data['checkout_token'])->where('id_user',$r->user()->id_user)->first();
        if($old) return redirect()->route('orders.show',$old->id_order);
        if(!hash_equals((string)$r->session()->get('checkout_token',''),$data['checkout_token'])) abort(419);
        $id=$service->checkout($r->user(),$data['alamat_pengiriman'],$data['checkout_token']);
        $r->session()->forget('checkout_token');
        return redirect()->route('orders.show',$id)->with('success','Checkout berhasil. Pesanan tersimpan dan keranjang dikosongkan.');
    }
    public function orders(Request $r) {
        return view('orders',['orders'=>DB::table('orders')->where('id_user',$r->user()->id_user)->orderByDesc('tanggal_order')->get()]);
    }
    public function order(Request $r,string $id) {
        $order=DB::table('orders')->where('id_order',$id)->where('id_user',$r->user()->id_user)->first();
        abort_unless($order,404);
        $details=DB::table('order_details')->join('products','products.id_barang','=','order_details.id_barang')->where('id_order',$id)->get();
        return view('order',['order'=>$order,'details'=>$details]);
    }
}
