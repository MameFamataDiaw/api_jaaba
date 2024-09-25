<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nom',30);
            $table->string('prenom',50);
            $table->string('telephone');
            $table->string('adresse');
            $table->string('email')->unique();
            $table->string('photo')->nullable();
            $table->enum('role', ['vendeur', 'client', 'livreur', 'admin']);
            $table->string('password');
            $table->boolean('statut')->default(true);
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
