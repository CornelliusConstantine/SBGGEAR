<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Check if we need to migrate old data (if the old columns exist)
        if (Schema::hasColumn('product_comments', 'admin_reply')) {
            // Get all comments with admin replies
            $comments = DB::table('product_comments')
                ->whereNotNull('admin_reply')
                ->get();
            
            // Insert replies into new table
            foreach ($comments as $comment) {
                if ($comment->admin_reply) {
                    $userId = $comment->replied_by ?? 1; // Default to user ID 1 if not set
                    
                    DB::table('product_comment_replies')->insert([
                        'comment_id' => $comment->id,
                        'user_id' => $userId,
                        'reply' => $comment->admin_reply,
                        'is_admin' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
            
            // Remove old columns
            Schema::table('product_comments', function (Blueprint $table) {
                if (Schema::hasColumn('product_comments', 'admin_reply')) {
                    $table->dropColumn('admin_reply');
                }
                if (Schema::hasColumn('product_comments', 'replied_by')) {
                    $table->dropColumn('replied_by');
                }
                if (Schema::hasColumn('product_comments', 'replied_at')) {
                    $table->dropColumn('replied_at');
                }
            });
        }
    }

    public function down()
    {
        // This migration is not reversible in a clean way
        // We've dropped columns and potentially combined multiple replies
    }
}; 