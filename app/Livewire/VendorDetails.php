<?php

namespace App\Livewire;

use App\Enums\RolesEnum;
use App\Enums\VendorStatusEnum;
use App\Models\Vendor;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class VendorDetails extends Component
{
    use WithFileUploads;

    // Onboarding (new vendor)
    public bool $showOnboardingForm = false;

    public string $new_store_name = '';

    public string $new_store_address = '';

    public $new_cover_image = null;

    // Update (existing vendor)
    public string $store_name = '';

    public string $store_address = '';

    public $cover_image = null;

    public bool $changeCover = false;

    /**
     * Load existing vendor data when the component mounts.
     */
    public function mount(): void
    {
        $user = Auth::user();

        if ($user->vendor) {
            $this->store_name = $user->vendor->store_name ?? '';
            $this->store_address = $user->vendor->store_address ?? '';
        }
    }

    // ── Onboarding ─────────────────────────────────────────────────────────────

    /**
     * Show the vendor onboarding form.
     */
    public function confirmBecome(): void
    {
        $this->showOnboardingForm = true;
    }

    /**
     * Create a new vendor profile for the current user and assign the vendor role.
     */
    public function becomeVendor(): void
    {
        $this->validate([
            'new_store_name' => ['required', 'string', 'max:255'],
            'new_store_address' => ['nullable', 'string', 'max:500'],
            'new_cover_image' => ['nullable', 'image', 'max:2048'],
        ]);

        $user = Auth::user();

        if ($user->vendor) {
            return;
        }

        $coverPath = null;
        if ($this->new_cover_image) {
            $coverPath = $this->new_cover_image->store('vendors/images', 'public');
        }

        $storeName = trim(preg_replace('/[\s\-]+/u', '-', mb_strtolower($this->new_store_name)), '-');

        Vendor::create([
            'user_id' => $user->id,
            'store_name' => $storeName,
            'store_address' => $this->new_store_address ?: null,
            'cover_image' => $coverPath,
            'status' => VendorStatusEnum::Approved->value,
        ]);

        $user->assignRole(RolesEnum::Vendor);

        $this->store_name = $storeName;
        $this->store_address = $this->new_store_address;

        session()->flash('success', 'Welcome! Your vendor profile has been created.');

        $this->redirect(route('profile.edit'), navigate: false);
    }

    // ── Update ─────────────────────────────────────────────────────────────────

    /**
     * Update the existing vendor's store name, address, and cover image.
     */
    public function updateVendor(): void
    {
        $this->validate([
            'store_name' => ['required', 'string', 'max:255'],
            'store_address' => ['nullable', 'string', 'max:500'],
            'cover_image' => ['nullable', 'image', 'max:2048'],
        ]);

        $user = Auth::user();

        if (! $user->vendor) {
            return;
        }

        $data = [
            'store_name' => trim(preg_replace('/[\s\-]+/u', '-', mb_strtolower($this->store_name)), '-'),
            'store_address' => $this->store_address,
        ];

        if ($this->cover_image) {
            $data['cover_image'] = $this->cover_image->store('vendors/images', 'public');
        }

        $user->vendor->update($data);

        $this->store_name = $user->vendor->fresh()->store_name;
        $this->cover_image = null;
        $this->changeCover = false;

        $this->dispatch('notify', message: 'Profile updated successfully.', type: 'success');
    }

    /**
     * Render the vendor details component with current user and status labels.
     */
    public function render()
    {
        return view('livewire.vendor-details', [
            'user' => Auth::user()->load('vendor'),
            'vendorStatusLabels' => VendorStatusEnum::labels(),
        ]);
    }
}
