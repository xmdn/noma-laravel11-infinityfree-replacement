<?php

namespace App\Infrastructure\Cart;

use App\Domain\Cart\CartRepository;
use Illuminate\Contracts\Session\Session;

final readonly class SessionCartRepository implements CartRepository
{
    private const KEY = 'store.cart';

    public function __construct(private Session $session) {}

    public function quantities(): array
    {
        return $this->session->get(self::KEY, []);
    }

    public function put(string $productId, int $quantity): void
    {
        $lines = $this->quantities();
        $lines[$productId] = min(10, max(1, $quantity));
        $this->session->put(self::KEY, $lines);
    }

    public function remove(string $productId): void
    {
        $lines = $this->quantities();
        unset($lines[$productId]);
        $this->session->put(self::KEY, $lines);
    }

    public function clear(): void
    {
        $this->session->forget(self::KEY);
    }
}
