<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('sn_comments', function (Blueprint $table) {
            $table->comment('评论');
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('team_id')->nullable()->comment('团队ID');
            $table->string('scope_type')->nullable()->comment('范围类型');
            $table->unsignedBigInteger('scope_id')->default(0)->comment('范围');

            $table->morphs('commentable');
            $table->unsignedBigInteger('parent_id')->default(0)->comment('上级');

            $table->morphs('commenter');
            $table->string('commenter_name')->nullable()->comment('评论者昵称');
            $table->string('commenter_avatar_url')->nullable()->comment('评论者头像');

            $table->nullableMorphs('be_replyer');
            $table->string('be_replyer_name')->nullable()->comment('被回复者昵称');
            $table->string('be_replyer_avatar_url')->nullable()->comment('被回复者头像');

            $table->string('content', 2048)->nullable()->comment('评论内容');
            $table->json('images')->nullable()->comment('评论图片');

            $table->unsignedBigInteger('like_num')->default(0)->comment('喜欢数量');
            $table->unsignedBigInteger('comment_num')->default(0)->comment('评论数量');
            $table->string('from_district', 60)->nullable()->comment('来源区域');
            $table->string('status', 20)->comment('状态');
            $table->json('options')->nullable()->comment('选项');
            $table->timestamps();
            $table->softDeletes();
            $table->index('team_id');
            $table->index(['scope_type', 'scope_id']);
            $table->index('parent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sn_comments');
    }
};
