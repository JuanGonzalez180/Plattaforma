<div class="form-row">
    <div class="form-group col-md-12">
        <label for="title">@lang('Title')</label>
        <input type="text" class="form-control" name="title" id="title" placeholder="@lang('Title')" value="{{ old('title', $staticContent->title) }}">

        @error('title')
            <span class="text-danger" role="alert">
                <small><b>{{ $message }}</b></small>
            </span>
        @enderror
    </div>
    <div class="form-group col-md-12">
        <label for="content">@lang('Content')</label>
        <textarea class="form-control" name="content" id="CKEditorWYSIWYG" placeholder="@lang('Content')">
            {{ old('content', $staticContent->content) }}
        </textarea>

        @error('content')
            <span class="text-danger" role="alert">
                <small><b>{{ $message }}</b></small>
            </span>
        @enderror
    </div>
</div>
<a type="button" class="btn btn-danger" href="{{ route('staticcontent.index') }}"><span class="oi oi-x" title="{{ $btnTextCancel }}" aria-hidden="true"></span> {{ $btnTextCancel }}</a>
<button type="submit" class="btn btn-primary"><span class="oi oi-check" title="{{ $btnTextPrimary }}" aria-hidden="true"></span> {{ $btnTextPrimary }}</button>

@section('js')
    <script src="https://cdn.ckeditor.com/ckeditor5/25.0.0/classic/ckeditor.js"></script>

    <script>
        ClassicEditor
            .create( document.querySelector( '#CKEditorWYSIWYG' ) )
            .catch( error => {
                console.error( error );
            } );
    </script>
@endsection