<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('users', function(Blueprint $t) {
            $t->string('id_user',15)->primary(); $t->string('nama_lengkap',100);
            $t->string('email',100)->unique(); $t->string('username',50)->unique();
            $t->string('password',255); $t->string('no_hp',15); $t->text('alamat'); $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('users'); }
};
