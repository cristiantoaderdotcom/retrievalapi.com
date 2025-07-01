<?php

namespace Database\Seeders;

use App\Models\Review;
use Illuminate\Database\Seeder;

class Reviews extends Seeder {
	/**
	 * Seed the application's database.
	 */
	public function run(): void {
		Review::query()->truncate();
		Review::query()->insert([
			[
				'name' => 'Dean',
				'title' => 'So easy to use and customize!',
				'text' => 'I was blown away by how simple it was to set up Page Widgets on my website. I could quickly add contact forms, links, and even review integrations. It\'s perfect for small business owners like me who don\'t have a tech background. Highly recommend!',
				'avatar' => url('/assets/images/reviews/144885927.jpg'),
				'source' => ''
			], [
				'name' => 'Mustafa',
				'title' => 'Amazing support and versatility!',
				'text' => 'Page Widgets has been a game-changer for our business. It allows us to engage with our customers through multiple channels, all in one place. The team behind it is super responsive and helpful, too!',
				'avatar' => url('/assets/images/reviews/683827731.jpg'),
				'source' => ''
			],  [
				'name' => 'Roxana',
				'title' => 'Best widget platform I’ve used!',
				'text' => 'I love how lightweight and efficient Page Widgets is. It doesn’t slow down my site and has so many customization options! The constant updates and new features make it even better.',
				'avatar' => url('/assets/images/reviews/83039721.jpg'),
				'source' => ''
			],  [
				'name' => 'Artur',
				'title' => 'Excellent for any website!',
				'text' => 'Page Widgets offers so much flexibility. I’ve been able to integrate everything from social media buttons to discount codes seamlessly. It’s easy to set up and works flawlessly.',
				'avatar' => url('/assets/images/reviews/64882771.jpg'),
				'source' => ''
			],  [
				'name' => 'Florian',
				'title' => 'Great product, even better team!',
				'text' => 'Page Widgets has exceeded my expectations. It\'s packed with features like cookie banners, contact options, and even social integrations. Plus, the support team always listens to user feedback and keeps improving the platform!',
				'avatar' => url('/assets/images/reviews/1298471947.jpg'),
				'source' => ''
			], [
				'name' => 'Adrian',
				'title' => 'Highly recommend PageWidgets!',
				'text' => 'I love how easy it is to use PageWidgets. It\'s a game-changer for my website.',
				'avatar' => url('/assets/images/reviews/470214580.jpg'),
				'source' => 'https://instagram.com/instaeadrian'
			],
		]);
	}
}
