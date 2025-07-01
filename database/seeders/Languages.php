<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;

class Languages extends Seeder {
	/**
	 * Seed the application's database.
	 */
	public function run(): void {
		$languages = [
			[
				'name' => 'English',
				'code' => 'en',
				'locale' => 'en_US',
			],	
			[
				'name' => 'Romanian',
				'code' => 'ro',
				'locale' => 'ro_RO',
			],
			[
				'name' => 'Spanish',
				'code' => 'es',
				'locale' => 'es_ES',
			],	
			[
				'name' => 'French',
				'code' => 'fr',
				'locale' => 'fr_FR',
			],		
			[
				'name' => 'German',
				'code' => 'de',
				'locale' => 'de_DE',
			],	
			[
				'name' => 'Italian',	
				'code' => 'it',
				'locale' => 'it_IT',
			],			
			[
				'name' => 'Portuguese',	
				'code' => 'pt',
				'locale' => 'pt_PT',
			],
			[
				'name' => 'Dutch',	
				'code' => 'nl',
				'locale' => 'nl_NL',
			],
			[
				'name' => 'Russian',	
				'code' => 'ru',
				'locale' => 'ru_RU',
			],
			[
				'name' => 'Turkish',	
				'code' => 'tr',
				'locale' => 'tr_TR',
			],
			[
				'name' => 'Arabic',	
				'code' => 'sa',
				'locale' => 'ar_SA',
			],
			[
				'name' => 'Chinese',	
				'code' => 'cn',
				'locale' => 'zh_CN',
			],
			[
				'name' => 'Japanese',	
				'code' => 'jp',
				'locale' => 'ja_JP',
			],
			[
				'name' => 'Korean',	
				'code' => 'kr',
				'locale' => 'ko_KR',
			],
			[
				'name' => 'Bulgarian',	
				'code' => 'bg',
				'locale' => 'bg_BG',
			],
			[
				'name' => 'Czech',	
				'code' => 'cz',
				'locale' => 'cs_CZ',
			],
			[
				'name' => 'Danish',	
				'code' => 'dk',
				'locale' => 'da_DK',
			],
			[
				'name' => 'Estonian',	
				'code' => 'ee',
				'locale' => 'et_EE',
			],
			[
				'name' => 'Finnish',	
				'code' => 'fi',
				'locale' => 'fi_FI',
			],
			[
				'name' => 'Greek',	
				'code' => 'gr',
				'locale' => 'el_GR',	
			],
			[
				'name' => 'Hungarian',	
				'code' => 'hu',
				'locale' => 'hu_HU',
			],	
			[
				'name' => 'Polish',	
				'code' => 'pl',
				'locale' => 'pl_PL',
			],
			[
				'name' => 'Ukrainian',	
				'code' => 'ua',
				'locale' => 'uk_UA',
			],
		];

		foreach ($languages as $language) {
			Language::query()->updateOrCreate(
				['code' => $language['code']],
				$language
			);
		}
	}
}
