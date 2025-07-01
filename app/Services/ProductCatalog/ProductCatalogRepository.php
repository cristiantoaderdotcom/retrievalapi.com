<?php

namespace App\Services\ProductCatalog;

use App\Models\Product;
use App\Models\ProductFeed;
use App\Models\ProductVariant;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ProductCatalogRepository
{
    /**
     * Get all product feeds for a workspace.
     *
     * @param int $workspaceId
     * @return Collection
     */
    public function getProductFeeds(int $workspaceId): Collection
    {
        return ProductFeed::where('workspace_id', $workspaceId)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get a product feed by ID.
     *
     * @param int $feedId
     * @param int $workspaceId
     * @return ProductFeed|null
     */
    public function getProductFeed(int $feedId, int $workspaceId): ?ProductFeed
    {
        return ProductFeed::where('id', $feedId)
            ->where('workspace_id', $workspaceId)
            ->first();
    }

    /**
     * Create a new product feed.
     *
     * @param array $data
     * @return ProductFeed
     */
    public function createProductFeed(array $data): ProductFeed
    {
        return ProductFeed::create($data);
    }

    /**
     * Update a product feed.
     *
     * @param ProductFeed $feed
     * @param array $data
     * @return ProductFeed
     */
    public function updateProductFeed(ProductFeed $feed, array $data): ProductFeed
    {
        $feed->update($data);
        return $feed;
    }

    /**
     * Delete a product feed.
     *
     * @param ProductFeed $feed
     * @return bool
     */
    public function deleteProductFeed(ProductFeed $feed): bool
    {
        return $feed->delete();
    }

    /**
     * Get products for a workspace with optional filters.
     *
     * @param int $workspaceId
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getProducts(int $workspaceId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Product::where('workspace_id', $workspaceId);

        // Apply filters
        if (!empty($filters['feed_id'])) {
            $query->where('product_feed_id', $filters['feed_id']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('handle', 'like', "%{$search}%")
                    ->orWhere('vendor', 'like', "%{$search}%")
                    ->orWhere('product_type', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['vendor'])) {
            $query->where('vendor', $filters['vendor']);
        }

        if (!empty($filters['product_type'])) {
            $query->where('product_type', $filters['product_type']);
        }

        // Apply sorting
        $sortField = $filters['sort_field'] ?? 'updated_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';
        $query->orderBy($sortField, $sortDirection);

        return $query->with(['variants', 'images'])->paginate($perPage);
    }

    /**
     * Get a product by ID.
     *
     * @param int $productId
     * @param int $workspaceId
     * @return Product|null
     */
    public function getProduct(int $productId, int $workspaceId): ?Product
    {
        return Product::where('id', $productId)
            ->where('workspace_id', $workspaceId)
            ->with(['variants', 'images', 'options', 'feed'])
            ->first();
    }

    /**
     * Get vendors for a workspace.
     *
     * @param int $workspaceId
     * @return array
     */
    public function getVendors(int $workspaceId): array
    {
        return Product::where('workspace_id', $workspaceId)
            ->whereNotNull('vendor')
            ->distinct()
            ->pluck('vendor')
            ->toArray();
    }

    /**
     * Get product types for a workspace.
     *
     * @param int $workspaceId
     * @return array
     */
    public function getProductTypes(int $workspaceId): array
    {
        return Product::where('workspace_id', $workspaceId)
            ->whereNotNull('product_type')
            ->distinct()
            ->pluck('product_type')
            ->toArray();
    }

    /**
     * Get product variants by product ID.
     *
     * @param int $productId
     * @return Collection
     */
    public function getProductVariants(int $productId): Collection
    {
        return ProductVariant::where('product_id', $productId)
            ->orderBy('position')
            ->get();
    }
} 