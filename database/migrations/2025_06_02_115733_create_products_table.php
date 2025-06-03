<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title')->comment('商品标题');
            $table->unsignedInteger('category_id')->index()->comment('商品分类ID');
            $table->tinyInteger('is_on_sale')->default(0)->comment('是否上架');
            $table->decimal('price', 10, 2)->default(0)->comment('商品价格');
            $table->string('pic_url')->default('')->comment('商品图片URL');
            $table->longText('attr')->comment('商品属性JSON');
            $table->timestamps();
            $table->softDeletes()->comment('软删除时间戳');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
