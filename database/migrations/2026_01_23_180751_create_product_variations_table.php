<?php

use App\Models\Product;
use App\Models\VariationType;
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
        Schema::create('variation_types', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Product::class)->index()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('type');
        });
        Schema::create('variation_type_options', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(VariationType::class)->index()->constrained()->cascadeOnDelete();
            $table->string('name');
        });
        Schema::create('product_variations', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Product::class)->index()->constrained()->cascadeOnDelete();
            $table->json('variation_type_option_ids');
            $table->integer('quantity')->nullable();
            $table->decimal('price', 20, 4)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variations');
    }
};
