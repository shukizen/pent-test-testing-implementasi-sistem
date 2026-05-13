<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Post;
use App\Models\Note;
use App\Models\ApiKey;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user - VULNERABLE A05: Default/predictable credentials
        $admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@pentest.local',
            'password' => 'admin123', // VULNERABLE A07: Weak password
            'role' => 'admin',
            'phone' => '081234567890',
            'ssn' => '3201234567890001', // VULNERABLE A02: NIK stored plaintext
            'bio' => 'System Administrator',
        ]);

        // Regular users
        $user1 = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@pentest.local',
            'password' => 'password', // VULNERABLE A07: Common password
            'role' => 'user',
            'phone' => '081234567891',
            'ssn' => '3201234567890002',
            'bio' => 'Mahasiswa Informatika',
        ]);

        $user2 = User::create([
            'name' => 'Siti Nurhaliza',
            'email' => 'siti@pentest.local',
            'password' => '123456', // VULNERABLE A07: Common password
            'role' => 'user',
            'phone' => '081234567892',
            'ssn' => '3201234567890003',
            'bio' => 'Dosen Keamanan Jaringan',
        ]);

        $user3 = User::create([
            'name' => 'Andi Wijaya',
            'email' => 'andi@pentest.local',
            'password' => 'qwerty', // VULNERABLE A07: Common password
            'role' => 'editor',
            'phone' => '081234567893',
            'ssn' => '3201234567890004',
            'bio' => 'Content Editor',
        ]);

        // Posts
        Post::create([
            'user_id' => $admin->id,
            'title' => 'Selamat Datang di Platform Pentest',
            'body' => '<h2>Welcome!</h2><p>Platform ini digunakan untuk latihan keamanan web. Silakan explore fitur-fitur yang ada.</p>',
            'is_published' => true,
        ]);

        Post::create([
            'user_id' => $user1->id,
            'title' => 'Tutorial Laravel untuk Pemula',
            'body' => '<p>Laravel adalah framework PHP yang populer untuk membangun aplikasi web modern.</p><p>Mari kita belajar bersama!</p>',
            'is_published' => true,
        ]);

        Post::create([
            'user_id' => $user2->id,
            'title' => 'Panduan Keamanan Web OWASP',
            'body' => '<p>OWASP Top 10 adalah daftar risiko keamanan web yang paling kritis.</p><ul><li>A01: Broken Access Control</li><li>A02: Cryptographic Failures</li></ul>',
            'is_published' => true,
        ]);

        Post::create([
            'user_id' => $user1->id,
            'title' => 'Draft Post - Rahasia Perusahaan',
            'body' => '<p>Ini adalah post draft yang berisi informasi sensitif internal. API Key production: sk-prod-xxxx-yyyy-zzzz</p>',
            'is_published' => false,
        ]);

        // Notes (private)
        Note::create([
            'user_id' => $admin->id,
            'title' => 'Kredensial Server Production',
            'content' => 'DB Host: 10.0.1.5, DB User: root, DB Pass: Sup3rS3cret!@#, SSH Key: /root/.ssh/id_rsa',
            'is_private' => true,
        ]);

        Note::create([
            'user_id' => $user1->id,
            'title' => 'Catatan Kuliah Keamanan',
            'content' => 'Minggu depan: presentasi tentang SQL Injection. Jangan lupa siapkan demo!',
            'is_private' => true,
        ]);

        Note::create([
            'user_id' => $user2->id,
            'title' => 'Rencana Proyek Akhir',
            'content' => 'Topik: Implementasi WAF untuk melindungi aplikasi dari serangan OWASP Top 10',
            'is_private' => true,
        ]);

        Note::create([
            'user_id' => $user2->id,
            'title' => 'Password Pribadi',
            'content' => 'Email: siti@gmail.com - pass: MyP@ssw0rd2024, Bank BCA: 1234567890 PIN: 123456',
            'is_private' => true,
        ]);

        // API Keys - VULNERABLE A02: Stored in plaintext
        ApiKey::create([
            'user_id' => $admin->id,
            'key' => md5('admin@pentest.local' . '2024-01-01'),
            'secret' => md5('1' . '2024-01-01'),
        ]);

        ApiKey::create([
            'user_id' => $user1->id,
            'key' => md5('budi@pentest.local' . '2024-01-01'),
            'secret' => md5('2' . '2024-01-01'),
        ]);
    }
}
