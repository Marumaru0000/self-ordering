<?php

declare(strict_types=1);

namespace Revolution\Ordering\Http\Livewire\Order;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\Redirector;
use GuzzleHttp\Client;
use Revolution\Ordering\Facades\Cart;
use Revolution\Ordering\Facades\Menu;

class History extends Component
{
    /**
     * @var Collection
     */
    protected Collection $menus;

    public function boot()
    {
        $this->menus = Collection::wrap(Menu::get());
    }

    /**
     * @return Collection
     */
    public function getHistoriesProperty(): Collection
    {
        return collect(session('history', []))->map([$this, 'replaceHistoryItems']);
    }

    /**
     * @param  array  $history
     * @return array
     */
    public function replaceHistoryItems(array $history): array
    {
        $menus = $this->getMenus();
        $history['items'] = Cart::items($history['items'], $menus)->toArray();
        return $history;
    }
    private function getMenus(): Collection
    {
        $client = new Client();
        $response = $client->get(env('ORDERING_MICROCMS_ENDPOINT'), [
            'headers' => ['X-API-KEY' => env('ORDERING_MICROCMS_API_KEY')]
        ]);
        $data = json_decode($response->getBody()->getContents(), true);
        return collect($data['contents'] ?? []);
    }

    /**
     * @return void
     */
    public function deleteHistory(): void
    {
        session()->forget('history');
    }

    /**
     * @return RedirectResponse|Redirector
     */
    public function back()
    {
        return redirect()->route('order');
    }

    public function render()
    {
        return view()->first([
            'ordering-theme::livewire.order.history',
            'ordering::livewire.order.history',
        ]);
    }
}