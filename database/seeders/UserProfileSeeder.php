<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserProfileSeeder extends Seeder
{
    public function run(): void
    {
        // Perfil do usuário admin
        User::updateOrCreate(
            ['email' => env('DAA_EMAIL', 'eu@darlisalvesamorim.dev')],
            [
                'name'      => env('DAA_NAME', 'Darlis Alves Amorim'),
                'email'     => env('DAA_EMAIL', 'eu@darlisalvesamorim.dev'),
                'password'  => Hash::make(env('DAA_PASSWORD', 'password')),
                'title'     => env('DAA_OFFICE'),
                'bio'       => env('DAA_DESCRIPTION'),
                'phone'     => env('DAA_PHONE'),
                'location'  => env('DAA_CITY') . ', ' . env('DAA_UF'),
                'website'   => 'https://darlisalvesamorim.dev',
                'github'    => env('DAA_GITHUB_URL') . env('DAA_GITHUB'),
                'linkedin'  => env('DAA_LINKEDIN_URL') . env('DAA_LINKEDIN'),
                'twitter'   => env('DAA_TWITTER_URL') . env('DAA_TWITTER'),
                'instagram' => env('DAA_INSTAGRAM_URL') . env('DAA_INSTAGRAM'),
                'youtube'   => env('DAA_YOUTUBE_URL') . env('DAA_YOUTUBE'),
            ]
        );

        // Identidade do site
        $identity = [
            'site.name'        => env('DAA_NAME'),
            'site.subname'     => env('DAA_SUBNAME'),
            'site.description' => env('DAA_DESCRIPTION'),
            'site.office'      => env('DAA_OFFICE'),
            'site.charset'     => env('DAA_CHARSET', 'UTF-8'),
        ];
        foreach ($identity as $key => $value) {
            Setting::set($key, $value, 'identity');
        }

        // SEO / Schema
        $seo = [
            'seo.schema'          => env('DAA_SCHEMA'),
            'seo.schema_og'       => env('DAA_SCHEMA_OPEN_GRAPH'),
            'seo.google_veri'     => env('DAA_GOOGLE_VERI'),
        ];
        foreach ($seo as $key => $value) {
            Setting::set($key, $value, 'seo');
        }

        // Assets
        $assets = [
            'assets.logotipo'     => env('DAA_LOGOTIPO'),
            'assets.avatar'       => env('DAA_AVATAR'),
            'assets.image_share'  => env('DAA_IMAGE_SHARE'),
            'assets.favicon'      => env('DAA_IMAGE_FAVICON'),
            'assets.cv'           => env('DAA_CV'),
        ];
        foreach ($assets as $key => $value) {
            Setting::set($key, $value, 'assets');
        }

        // Tipografia
        $typography = [
            'typography.font_name'   => env('DAA_FONT_NAME'),
            'typography.font_weight' => env('DAA_FONT_WEIGHT'),
        ];
        foreach ($typography as $key => $value) {
            Setting::set($key, $value, 'typography');
        }

        // Informações pessoais
        $personal = [
            'personal.email'    => env('DAA_EMAIL'),
            'personal.phone'    => env('DAA_PHONE'),
            'personal.addr'     => env('DAA_ADDR'),
            'personal.district' => env('DAA_DISTRICT'),
            'personal.city'     => env('DAA_CITY'),
            'personal.uf'       => env('DAA_UF'),
            'personal.zip'      => env('DAA_ZIP'),
            'personal.country'  => env('DAA_COUNTRY'),
            'personal.age'      => env('DAA_AGE'),
            'personal.cnpj'     => env('DAA_CNPJ'),
            'personal.ie'       => env('DAA_IE'),
        ];
        foreach ($personal as $key => $value) {
            Setting::set($key, $value, 'personal');
        }

        // Redes sociais
        $social = [
            'social.links'       => env('DAA_LINKS_URL') . env('DAA_LINKS'),
            'social.linkedin'    => env('DAA_LINKEDIN_URL') . env('DAA_LINKEDIN'),
            'social.github'      => env('DAA_GITHUB_URL') . env('DAA_GITHUB'),
            'social.twitter'     => env('DAA_TWITTER_URL') . env('DAA_TWITTER'),
            'social.instagram'   => env('DAA_INSTAGRAM_URL') . env('DAA_INSTAGRAM'),
            'social.tiktok'      => env('DAA_TIKTOK_URL') . env('DAA_TIKTOK'),
            'social.youtube'     => env('DAA_YOUTUBE_URL') . env('DAA_YOUTUBE'),
            'social.fb'          => env('DAA_FB_URL') . env('DAA_FB'),
            'social.fb_page'     => env('DAA_FB_PAGE_URL') . env('DAA_FB_PAGE'),
            'social.medium'      => env('DAA_MEDIUM_URL') . env('DAA_MEDIUM'),
            'social.devto'       => env('DAA_DEVTO_URL') . env('DAA_DEVTO'),
            'social.codepen'     => env('DAA_CODEPEN_URL') . env('DAA_CODEPEN'),
            'social.behance'     => env('DAA_BEHANCE_URL') . env('DAA_BEHANCE'),
            'social.dribbble'    => env('DAA_DRIBBBLE_URL') . env('DAA_DRIBBBLE'),
            'social.deviantart'  => env('DAA_DEVIANTART_URL') . env('DAA_DEVIANTART'),
            'social.pinterest'   => env('DAA_PINTEREST_URL') . env('DAA_PINTEREST'),
        ];
        foreach ($social as $key => $value) {
            Setting::set($key, $value, 'social');
        }

        // E-mail
        $mail = [
            'mail.from_address' => env('MAIL_FROM_ADDRESS'),
            'mail.from_name'    => env('MAIL_FROM_NAME'),
        ];
        foreach ($mail as $key => $value) {
            Setting::set($key, $value, 'mail');
        }
    }
}
