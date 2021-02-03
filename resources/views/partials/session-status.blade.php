@if ( session('status') )
<div class="alert alert-success alert dismissible fade show" role="alert">
    @switch( session('status') )
        @case('create')
            {{ session('title') }} @lang('was created successfully')
            @break

        @case('edit')
            {{ session('title') }} @lang('was edited successfully')
            @break

        @case('delete')
            {{ session('title') }} @lang('was removed successfully')
            @break
        @default
            {{ session('status') }}
    @endswitch
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif