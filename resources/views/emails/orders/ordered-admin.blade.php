<x-mail::message>

Nova porudžbina.

#{{$order->id}} <br/>
Datum: {{$order->ordered}}
Količina: {{$order->quantity}} <br/>
Ukupna cena: {{$order->price}} RSD <br/>

Ime i prezime: {{$order->first_name}} {{$order->last_name}} <br/>
Adresa: {{$order->address}}, {{$order->zip}} {{$order->city}} <br/>
Telefon: {{$order->phone}} <br/>
Email: {{$order->email}} <br/>

<br>
{{ config('app.name') }}
</x-mail::message>
