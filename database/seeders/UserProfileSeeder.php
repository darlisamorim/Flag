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
        // ─── Perfil do usuário Super Admin ────────────────────────
        $user = User::where('email', env('DAA_EMAIL', 'eu@darlisalvesamorim.dev'))->first();

        $data = [
            'name'          => env('DAA_NAME'),
            'email'         => env('DAA_EMAIL'),
            'access_level'  => 'super_admin',
            'title'         => env('DAA_OFFICE'),
            'role'          => env('DAA_ROLE'),
            'subname'       => env('DAA_SUBNAME'),
            'bio'           => env('DAA_DESCRIPTION'),
            'phone'         => env('DAA_PHONE'),
            'location'      => env('DAA_CITY') . ', ' . env('DAA_UF'),
            'addr'          => env('DAA_ADDR'),
            'district'      => env('DAA_DISTRICT'),
            'zip'           => env('DAA_ZIP'),
            'country'       => env('DAA_COUNTRY'),
            'razao_social'  => env('DAA_RAZAO_SOCIAL'),
            'nome_fantasia' => env('DAA_NOME_FANTASMA'),
            'cnpj'          => env('DAA_CNPJ'),
            'ie'            => env('DAA_IE'),
            'website'       => 'https://darlisalvesamorim.dev',
            'links'         => env('DAA_LINKS_URL') . env('DAA_LINKS'),
            'github'        => env('DAA_GITHUB_URL') . env('DAA_GITHUB'),
            'linkedin'      => env('DAA_LINKEDIN_URL') . env('DAA_LINKEDIN'),
            'twitter'       => env('DAA_TWITTER_URL') . env('DAA_TWITTER'),
            'instagram'     => env('DAA_INSTAGRAM_URL') . env('DAA_INSTAGRAM'),
            'tiktok'        => env('DAA_TIKTOK_URL') . env('DAA_TIKTOK'),
            'youtube'       => env('DAA_YOUTUBE_URL') . env('DAA_YOUTUBE'),
            'facebook'      => env('DAA_FB_URL') . env('DAA_FB'),
            'fb_page'       => env('DAA_FB_PAGE_URL') . env('DAA_FB_PAGE'),
            'medium'        => env('DAA_MEDIUM_URL') . env('DAA_MEDIUM'),
            'devto'         => env('DAA_DEVTO_URL') . env('DAA_DEVTO'),
            'codepen'       => env('DAA_CODEPEN_URL') . env('DAA_CODEPEN'),
            'behance'       => env('DAA_BEHANCE_URL') . env('DAA_BEHANCE'),
            'dribbble'      => env('DAA_DRIBBBLE_URL') . env('DAA_DRIBBBLE'),
            'deviantart'    => env('DAA_DEVIANTART_URL') . env('DAA_DEVIANTART'),
            'pinterest'     => env('DAA_PINTEREST_URL') . env('DAA_PINTEREST'),
        ];

        // Só define a senha se o usuário não existe ainda
        if (!$user) {
            $data['password'] = Hash::make(env('DAA_PASSWORD', 'password'));
            User::create($data);
        } else {
            // Atualiza tudo MENOS a senha
            $user->update($data);
        }

        // ─── Settings — Identidade ─────────────────────────────────
        $identity = [
            'site.name'        => env('DAA_NAME'),
            'site.subname'     => env('DAA_SUBNAME'),
            'site.description' => env('DAA_DESCRIPTION'),
            'site.office'      => env('DAA_OFFICE'),
            'site.role'        => env('DAA_ROLE'),
            'site.charset'     => env('DAA_CHARSET', 'UTF-8'),
        ];
        foreach ($identity as $key => $value) {
            Setting::set($key, $value, 'identity');
        }

        // ─── Settings — SEO ───────────────────────────────────────
        $seo = [
            'seo.schema'      => env('DAA_SCHEMA'),
            'seo.schema_og'   => env('DAA_SCHEMA_OPEN_GRAPH'),
            'seo.google_veri' => env('DAA_GOOGLE_VERI'),
        ];
        foreach ($seo as $key => $value) {
            Setting::set($key, $value, 'seo');
        }

        // ─── Settings — Assets ────────────────────────────────────
        $assets = [
            'assets.logotipo'    => env('DAA_LOGOTIPO'),
            'assets.avatar'      => env('DAA_AVATAR'),
            'assets.image_share' => env('DAA_IMAGE_SHARE'),
            'assets.favicon'     => env('DAA_IMAGE_FAVICON'),
            'assets.cv'          => env('DAA_CV'),
        ];
        foreach ($assets as $key => $value) {
            Setting::set($key, $value, 'assets');
        }

        // ─── Settings — Tipografia ────────────────────────────────
        $typography = [
            'typography.font_name'   => env('DAA_FONT_NAME'),
            'typography.font_weight' => env('DAA_FONT_WEIGHT'),
        ];
        foreach ($typography as $key => $value) {
            Setting::set($key, $value, 'typography');
        }

        // ─── Settings — Pessoal ───────────────────────────────────
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

        // ─── Settings — Redes sociais ─────────────────────────────
        $social = [
            'social.website'    => 'https://darlisalvesamorim.dev',
            'social.links'      => env('DAA_LINKS_URL') . env('DAA_LINKS'),
            'social.linkedin'   => env('DAA_LINKEDIN_URL') . env('DAA_LINKEDIN'),
            'social.github'     => env('DAA_GITHUB_URL') . env('DAA_GITHUB'),
            'social.twitter'    => env('DAA_TWITTER_URL') . env('DAA_TWITTER'),
            'social.instagram'  => env('DAA_INSTAGRAM_URL') . env('DAA_INSTAGRAM'),
            'social.tiktok'     => env('DAA_TIKTOK_URL') . env('DAA_TIKTOK'),
            'social.youtube'    => env('DAA_YOUTUBE_URL') . env('DAA_YOUTUBE'),
            'social.fb'         => env('DAA_FB_URL') . env('DAA_FB'),
            'social.fb_page'    => env('DAA_FB_PAGE_URL') . env('DAA_FB_PAGE'),
            'social.medium'     => env('DAA_MEDIUM_URL') . env('DAA_MEDIUM'),
            'social.devto'      => env('DAA_DEVTO_URL') . env('DAA_DEVTO'),
            'social.codepen'    => env('DAA_CODEPEN_URL') . env('DAA_CODEPEN'),
            'social.behance'    => env('DAA_BEHANCE_URL') . env('DAA_BEHANCE'),
            'social.dribbble'   => env('DAA_DRIBBBLE_URL') . env('DAA_DRIBBBLE'),
            'social.deviantart' => env('DAA_DEVIANTART_URL') . env('DAA_DEVIANTART'),
            'social.pinterest'  => env('DAA_PINTEREST_URL') . env('DAA_PINTEREST'),
        ];
        foreach ($social as $key => $value) {
            Setting::set($key, $value, 'social');
        }

        // ─── Settings — E-mail ────────────────────────────────────
        $mail = [
            'mail.from_address' => env('MAIL_FROM_ADDRESS'),
            'mail.from_name'    => env('MAIL_FROM_NAME'),
        ];
        foreach ($mail as $key => $value) {
            Setting::set($key, $value, 'mail');
        }
    }
}
