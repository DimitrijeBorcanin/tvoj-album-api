<x-mail::message>

Nažalost, tvoja porudžbina je odbijena. Detalje možeš videti ispod.

Razlog odbijanja: <br />
{{$order->note}}

#{{$order->id}} <br/>
Datum: {{$order->ordered}}
Količina: {{$order->quantity}} <br/>
Ukupna cena: {{$order->price}} RSD <br/>

Ime i prezime: {{$order->first_name}} {{$order->last_name}} <br/>
Adresa: {{$order->address}}, {{$order->zip}} {{$order->city}} <br/>
Telefon: {{$order->phone}} <br/>
Email: {{$order->email}} <br/>


{{-- <x-mail::button :url="''">
Button Text
</x-mail::button> --}}

<br>
{{ config('app.name') }}
</x-mail::message>
