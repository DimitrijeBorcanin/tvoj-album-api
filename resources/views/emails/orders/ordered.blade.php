<x-mail::message>

Tvoja porudžbina je uspešno napravljena i sada je u obradi. Uskoro ćeš dobiti mejl o potvrdi porudžbine.

#{{$order->id}} <br/>
Datum: {{$order->ordered}}
Količina: {{$order->quantity}} <br/>
Ukupna cena: {{$order->quantity * $order->price}} RSD <br/>

Ime i prezime: {{$order->first_name}} {{$order->last_name}} <br/>
Adresa: {{$order->address}}, {{$order->zip}} {{$order->city}} <br/>
Telefon: {{$order->phone}} <br/>
Email: {{$order->email}} <br/>

Status porudžbine možeš da pratiš i na našoj aplikaciji.
<br>
{{ config('app.name') }}
</x-mail::message>
