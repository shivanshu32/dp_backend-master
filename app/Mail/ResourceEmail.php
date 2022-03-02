<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\AccessCode;

class ResourceEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $user;
    public $order;
    public $orderArts;
    public $viewUrl;
    public $tempPassword;
    public function __construct($order)
    {
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
            $newCodeData = AccessCode::create([
                'order_id' => $this->order->id,
                'code' => md5(microtime(true))
            ]);
            $code = $newCodeData->code;
        }

        

        $message = $this->view('emails.resource-email',['order' => $this->order, 'code' => $code]) 
            ->subject('Order from District Printing');

        // if($this->order && $this->order->proof_url) {
        //     $message->attach($this->order->proof_url);
        // }

        // foreach($this->orderArts as $orderArt) {
        //     $message->attach($orderArt->file_url);
        // }

        return $message;
    }
}
