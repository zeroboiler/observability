<?php

declare(strict_types=1);

namespace ZeroBoiler\Observability\AutoInstrumentation;

use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Event;
use ZeroBoiler\Observability\Span;

final class MailInstrumentation extends BaseInstrumentation
{
    #[\Override]
    public function register(): void
    {
        Event::listen(MessageSending::class, function (MessageSending $event): void {
            $message = $event->message;

            // Use message ID as key to support concurrent sends
            $messageId = $message->getId() ?? spl_object_hash($message);

            $span = Span::start('mail.send', 'producer', [
                'messaging.system' => 'email',
                'messaging.operation' => 'send',
                'email.to' => implode(', ', array_keys($message->getTo() ?? [])),
                'email.cc' => implode(', ', array_keys($message->getCc() ?? [])),
                'email.bcc' => implode(', ', array_keys($message->getBcc() ?? [])),
                'email.subject' => $message->getSubject(),
            ]);

            app()->instance('observability.mail_span.'.$messageId, $span);
        });

        Event::listen(MessageSent::class, function (MessageSent $event): void {
            $message = $event->message;
            $messageId = $message->getId() ?? spl_object_hash($message);

            $span = app('observability.mail_span.'.$messageId, null);

            if ($span instanceof Span) {
                $span->end();
                app()->forgetInstance('observability.mail_span.'.$messageId);
            }
        });
    }

    #[\Override]
    protected function getKey(): string
    {
        return 'mail';
    }
}
