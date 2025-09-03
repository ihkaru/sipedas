<x-filament-panels::page>
    {{-- Form ini akan memanggil metode `save()` di kelas Pengaturan.php saat disubmit --}}
    <form wire:submit="save">
        {{-- Baris ini akan merender semua field form (Toggle dan TextInput) yang Anda definisikan di metode form() --}}
        {{ $this->form }}
        <br />
        {{-- Ini adalah tombol untuk menyimpan form --}}
        {{-- <x-filament::button type="submit" class="mt-4">
            Simpan Perubahan
        </x-filament::button> --}}
    </form>
</x-filament-panels::page>
