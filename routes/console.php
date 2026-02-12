<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('send-mail', function () {
    $this->info('🚀 Sending test email via Mailtrap SMTP...');

    try {
        // Enviar email usando configuración SMTP de Mailtrap
        Mail::raw('Congrats for sending test email with Mailtrap!', function ($message) {
            $message
                ->to('armingkings@gmail.com')
                ->from('hello@example.com', 'Mailtrap Test')
                ->subject('You are awesome!')
                ->getHeaders()
                ->addTextHeader('X-Mailtrap-Category', 'Integration Test');
        });

        $this->info('✅ Email sent successfully via SMTP!');
        $this->line('');
        $this->info('Email Details:');
        $this->line('  From: hello@example.com (Mailtrap Test)');
        $this->line('  To: armingkings@gmail.com');
        $this->line('  Subject: You are awesome!');
        $this->line('  Body: Congrats for sending test email with Mailtrap!');
        $this->line('  Category: Integration Test');
    } catch (\Exception $e) {
        $this->error('❌ Failed to send email: '.$e->getMessage());
        return 1;
    }

    return 0;
})->purpose('Send test email via Mailtrap SMTP');

Artisan::command('send-mail:api', function () {
    $this->info('🚀 Sending test email via Mailtrap API...');

    try {
        // Verificar si Mailtrap SDK está disponible
        if (!class_exists('Mailtrap\MailtrapClient')) {
            $this->warn('⚠️  Mailtrap SDK not installed.');
            $this->info('Install with: composer require mailtrap/mailtrap-php');
            $this->info('For now, using SMTP method instead...');
            $this->call('send-mail');
            return 0;
        }

        // Usar Mailtrap SDK para envío por API
        $email = (new \Mailtrap\Mime\MailtrapEmail())
            ->from(new \Symfony\Component\Mime\Address('hello@example.com', 'Mailtrap Test'))
            ->to(new \Symfony\Component\Mime\Address('armingkings@gmail.com'))
            ->subject('You are awesome!')
            ->category('Integration Test')
            ->text('Congrats for sending test email with Mailtrap!')
        ;

        $response = \Mailtrap\MailtrapClient::initSendingEmails(
            apiKey: config('services.mailtrap.api_token'),
            isSandbox: config('services.mailtrap.sandbox'),
            inboxId: config('services.mailtrap.inbox_id')
        )->send($email);

        $result = \Mailtrap\Helper\ResponseHelper::toArray($response);

        $this->info('✅ Email sent successfully via API!');
        $this->line('');
        $this->info('API Response:');
        $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    } catch (\Exception $e) {
        $this->error('❌ Failed to send email: '.$e->getMessage());
        return 1;
    }

    return 0;
})->purpose('Send test email via Mailtrap API (requires SDK)');
