<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResourceSend extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $email;
    public $body;
    public $file;
    public function __construct($email, $body, $file, $order)
    {
        $this->email = $email;
        $this->body = $body;
        $this->file = $file;
        $this->order = $order;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        try {
            $code = AccessCode::where('order_id',$this->order->id)->first()->code;
        }catch(\Exception $e){
            $code = null;
        }

        return $this->view('emails/resource-send',['order' => $this->order, $code => $code])
                ->subject('Resources')
                ->attach($this->file->getRealPath(),
                [
                    'as' => $this->file->getClientOriginalName(),
                    'mime' => $this->file->getClientMimeType(),
                ]);
    }
}
