<?php

declare(strict_types=1);

use App\Support\Database\CheckConstraint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('review_replies', function (Blueprint $table): void {
            $table->id();
            // One public reply per review, enforced by the unique index.
            $table->foreignId('review_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();
        });

        CheckConstraint::add('review_replies', 'review_replies_body_length_check', 'char_length(body) BETWEEN 1 AND 1000');
    }

    public function down(): void
    {
        Schema::dropIfExists('review_replies');
    }
};
