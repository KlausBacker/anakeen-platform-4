<?php

namespace Anakeen\SmartStructures\UiTest\TstUiAllType\Renders;

use SmartStructure\Attributes\Tst_ddui_alltype as myAttributes;

class AllRenderShowEmpty extends AllRenderConfigView
{

    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);

        $options->commonOption()->showEmptyContent("Aucune information");
        $options->file()->showEmptyContent("Aucun fichier n'a été enregistré");
        $options->image()->showEmptyContent("Aucune image n'a été fournie");
        $options->color()->showEmptyContent('<b style="color:green">Aucune couleur à afficher même en vert</b>');
        $options->date()->showEmptyContent("Aucune date à afficher");
        $options->time()->showEmptyContent("Pas le temps");
        $options->timestamp()->showEmptyContent("<b><i>J'ai dit pas le temps ni pas de date non plus</i></b>");
        $options->arrayAttribute()->showEmptyContent("<h1>Aucune valeur dans ce tableau</h1>");


        $options->tab()->showEmptyContent('<h1 style="color:blue">Aucune valeur dans cet onglet</h1>');
        $options->frame()->showEmptyContent('<h1 style="color:red">Aucune valeur dans ce cadre</h1>');
        // Need to reset showEmpty to override commonOption and type options
        $options->arrayAttribute(myAttributes::test_ddui_all__array_misc)->showEmptyContent(null);
        $options->color(myAttributes::test_ddui_all__color_array)->showEmptyContent(null);
        $options->password(myAttributes::test_ddui_all__password_array)->showEmptyContent(null);


        $options->arrayAttribute(myAttributes::test_ddui_all__array_files)->showEmptyContent(null);
        $options->file(myAttributes::test_ddui_all__file_array)->showEmptyContent(null);
        $options->image(myAttributes::test_ddui_all__image_array)->showEmptyContent(null);
        $options->frame(myAttributes::test_ddui_all__frame_files)->showEmptyContent(null);
        return $options;
    }
}
