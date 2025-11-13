@if ($getRecord()->sertificate_url)
    <div style="width: 100%; height: 600px; border: 1px solid #e0e0e0; overflow: hidden;">
        <iframe src="{{ $getRecord()->sertificate_url }}" style="width: 100%; height: 100%; border: none;"></iframe>
    </div>
@else
    <div style="padding: 20px; text-align: center; color: #888;">
        No certificate PDF generated yet.
    </div>
@endif
