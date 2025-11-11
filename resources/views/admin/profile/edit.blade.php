@extends('layouts.app')
@section('title','Profile')
@section('content')
<x-card class="p-6">
  <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @method('PUT')
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div>
        <label class="block text-sm mb-1">Name</label>
        <input name="name" value="{{ old('name', optional($profile)->name) }}" class="w-full rounded-lg border border-gray-300 px-3 py-2" />
      </div>
      <div>
        <label class="block text-sm mb-1">Role/Subtitle</label>
        <input name="role" value="{{ old('role', optional($profile)->role) }}" class="w-full rounded-lg border border-gray-300 px-3 py-2" />
      </div>
    </div>
    <div>
      <label class="block text-sm mb-1">Profile Photo</label>
      <input type="file" name="photo" accept="image/*" />
      @if(optional($profile)->photo_path)
        <div class="mt-3 flex items-center gap-4">
          <img src="{{ asset('storage/'. $profile->photo_path) }}" class="w-24 h-24 rounded-full object-cover" />
          <label class="inline-flex items-center gap-2 text-sm text-gray-600"><input type="checkbox" name="remove_photo" value="1" class="rounded"> Remove current photo</label>
        </div>
      @endif
    </div>
    <div class="text-right">
      <x-ui.button>Save</x-ui.button>
    </div>
  </form>
</x-card>
@endsection

