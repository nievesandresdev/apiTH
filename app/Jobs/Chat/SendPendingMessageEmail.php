<?php

namespace App\Jobs\Chat;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendPendingMessageEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $mailData;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $mailData)
    {
        $this->mailData = $mailData;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        Log::info('Sending pending message email');
        $guest = $this->mailData['guest'];
        /* $stay = $this->mailData['stay'];
        $msg = $this->mailData['msg']; */
        $messageContent = $this->mailData['messageContent'];

        Mail::send('emails.chat.pending-message', [
            'messageContent' => $messageContent,
            //'messageUrl' => url('/messages/'.$msg->id)
        ], function($message) use ($guest) {
            $message->to($guest->email)
                    ->subject('Tienes un mensaje pendiente');
        });
        Log::info('Pending message email sent',$guest->email);
    }
}
