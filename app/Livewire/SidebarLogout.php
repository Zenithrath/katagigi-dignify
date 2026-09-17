<?php

namespace App\Livewire;

use App\Livewire\Actions\Logout;
use Livewire\Component;

class SidebarLogout extends Component
{
    public bool $collapsible = false;

    public function mount(bool $collapsible = false): void
    {
        $this->collapsible = $collapsible;
    }

    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }

    public function render()
    {
        return view('livewire.sidebar-logout');
    }
}
