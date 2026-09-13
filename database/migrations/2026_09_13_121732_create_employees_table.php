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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();

            /** Generated as RR0001. The business key used by imports and exports. */
            $table->string('employee_number')->unique();
            $table->string('name');
            /** Also the attendance binding key, so it must be unique. */
            $table->string('phone')->unique();

            /** Identity */
            $table->string('national_id', 32)->nullable()->unique();
            $table->string('gender', 20)->nullable();
            $table->string('religion', 20)->nullable();
            $table->string('marital_status', 20)->nullable();
            $table->string('birth_place')->nullable();
            $table->date('birth_date')->nullable();

            /** Address on the identity card */
            $table->string('identity_address')->nullable();
            $table->foreignId('identity_province_id')->nullable()->constrained('provinces')->nullOnDelete();
            $table->foreignId('identity_city_id')->nullable()->constrained('cities')->nullOnDelete();

            /** Address actually lived at */
            $table->string('domicile_address')->nullable();
            $table->foreignId('domicile_province_id')->nullable()->constrained('provinces')->nullOnDelete();
            $table->foreignId('domicile_city_id')->nullable()->constrained('cities')->nullOnDelete();

            /**
             * Placement. The company is reached through the branch, so it is not repeated here.
             */
            $table->foreignId('branch_id')->constrained()->restrictOnDelete();
            $table->foreignId('department_id')->constrained()->restrictOnDelete();
            $table->foreignId('position_id')->constrained()->restrictOnDelete();
            $table->foreignId('job_level_id')->nullable()->constrained()->nullOnDelete();

            /** Employment */
            $table->date('join_date');
            $table->date('contract_ends_on')->nullable();
            $table->string('employment_status', 20)->default('active');
            $table->date('termination_date')->nullable();

            /** Optional, only when an attendance machine is used. */
            $table->string('fingerprint_id', 64)->nullable()->unique();

            $table->timestamps();
            $table->softDeletes();

            $table->index('employment_status');
            $table->index(['branch_id', 'employment_status']);
            $table->index(['department_id', 'employment_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
