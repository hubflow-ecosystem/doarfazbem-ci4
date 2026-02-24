<?php

namespace App\Controllers;

class Pages extends BaseController
{
    /**
     * Página "Anuncie Conosco"
     * Mostra informações sobre publicidade na plataforma
     */
    public function anuncieConosco()
    {
        $data = [
            'title' => 'Anuncie Conosco | DoarFazBem',
            'description' => 'Anuncie sua marca na maior plataforma de crowdfunding social do Brasil'
        ];

        return view('pages/anuncie_conosco', $data);
    }

    /**
     * Página "Doe para Plataforma"
     * Mostra informações sobre como apoiar a plataforma
     */
    public function doeParaPlataforma()
    {
        $data = [
            'title' => 'Doe para Plataforma | DoarFazBem',
            'description' => 'Ajude a manter a plataforma DoarFazBem no ar'
        ];

        return view('pages/doe_plataforma', $data);
    }

    /**
     * Página "Sobre"
     * Mostra informações sobre a plataforma
     */
    public function sobre()
    {
        $data = [
            'title' => 'Sobre | DoarFazBem',
            'description' => 'Conheça a DoarFazBem, a plataforma de crowdfunding mais justa do Brasil'
        ];

        return view('pages/sobre', $data);
    }

    /**
     * Página "Como Funciona"
     * Explica o funcionamento da plataforma
     */
    public function comoFunciona()
    {
        $data = [
            'title' => 'Como Funciona | DoarFazBem',
            'description' => 'Entenda como funciona a DoarFazBem e como criar sua campanha'
        ];

        return view('pages/como_funciona', $data);
    }

    /**
     * Página "Termos de Uso"
     * Exibe os termos e condições da plataforma
     */
    public function termos()
    {
        $data = [
            'title' => 'Termos de Uso | DoarFazBem',
            'description' => 'Termos e condições de uso da plataforma DoarFazBem'
        ];

        return view('pages/termos', $data);
    }

    /**
     * Página "Política de Privacidade"
     * Exibe a política de privacidade da plataforma
     */
    public function privacidade()
    {
        $data = [
            'title' => 'Política de Privacidade | DoarFazBem',
            'description' => 'Como tratamos e protegemos seus dados pessoais'
        ];

        return view('pages/privacidade', $data);
    }
}
