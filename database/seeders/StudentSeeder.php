<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Student;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $students = [
            ['full_name' => 'สมชาย ใจดี', 'student_id' => '35420'],
            ['full_name' => 'สมหญิง ดีงาม', 'student_id' => '35421'],
            ['full_name' => 'สายฝน เย็นใจ', 'student_id' => '35422'],
            ['full_name' => 'อดิศักดิ์ สายลม', 'student_id' => '35423'],
            ['full_name' => 'มนัส สุขสบาย', 'student_id' => '35424'],
            ['full_name' => 'ศิริพร กล้าหาญ', 'student_id' => '35425'],
            ['full_name' => 'อารยา สมบูรณ์', 'student_id' => '35426'],
            ['full_name' => 'จิราพร เพียรดี', 'student_id' => '35427'],
            ['full_name' => 'ธีระชัย เก่งกล้า', 'student_id' => '35428'],
            ['full_name' => 'พิมพ์ชนก ใจบุญ', 'student_id' => '35429'],
        ];

        foreach ($students as $student) {
            Student::firstOrCreate($student);
        }
    }
}
