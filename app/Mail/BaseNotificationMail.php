<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Enums\SubscriptionTypes;

abstract class BaseNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $subscriber;
    public $additinalData;
    public $priceSubscriptionType = SubscriptionTypes::PRICE->value;
    public $percentageSubscriptionType = SubscriptionTypes::PERCENTAGE->value;

    public function __construct($subscriber, $additinalData)
    {
        $this->subscriber = $subscriber;
        $this->additinalData = $additinalData;
    }

    public abstract function getEnvelopeSubject(): string;

    public abstract function getMailType(): string;

     /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->getEnvelopeSubject(),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mails.notification',
            with: [
                'subscriber' => $this->subscriber,
                'additinalData'   => $this->additinalData,
                'mailType'   => $this->getMailType(),
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
