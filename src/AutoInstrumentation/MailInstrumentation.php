<?php

declare(strict_types=1);

namespace ZeroBoiler\Observability\AutoInstrumentation;

use Illuminate\Support\Facades\Event;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;
use ZeroBoiler\Observability\Span;

final class MailInstrumentation extends BaseInstrumentation
{
    #[\Override]
    protected function getKey(): string
    {
        return 'mail';
    }

    #[\Override]
    public function register(): void
    {
        Event::listen(MessageSending::class, function (MessageSending $event) {
            $message = $event->message;

            $span = Span::start('mail.send', 'producer', [
                'messaging.system' => 'email',
                'messaging.operation' => 'send',
                'email.to' => implode(', ', array_keys($message->getTo() ?? [])),
                'email.cc' => implode(', ', array_keys($message->getCc() ?? [])),
                'email.bcc' => implode(', ', array_keys($message->getBcc() ?? [])),
                'email.subject' => $message->getSubject(),
            ]);

            app()->instance('observability.current_mail_span', $span);
        });

        Event::listen(MessageSent::class, function (MessageSent $event) {
            $span = app('observability.current_mail_span');

            if ($span) {
                $span->end();
            }
        });
    }
}