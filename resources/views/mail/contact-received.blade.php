<x-mail::message>
# New message from the contact form

**From:** {{ $contactMessage->email }}

**Subject:** {{ $contactMessage->subject }}

<x-mail::panel>
{{ $contactMessage->message }}
</x-mail::panel>

<x-mail::button :url="$adminUrl">
Open admin
</x-mail::button>
</x-mail::message>
