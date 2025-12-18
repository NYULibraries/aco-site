@foreach ($title as $langCode => $data)
  <h2
    class="page-title"
    lang="{{ $langCode }}"
    dir="{{ $data['language']['dir'] }}"
  >
    <span>{{ $data['label'] }}</span>
  </h2>
@endforeach
