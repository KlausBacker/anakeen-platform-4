<?php

namespace Anakeen\Pu\Config;

use Anakeen\EnumItem;

class EnumExtendsTesting
{
    public function __invoke()
    {
        $items = [];
        $items[] = new EnumItem(
            "europa",
            "Europe",
            [
                new EnumItem("france", "France", [
                    new EnumItem("Paris"),
                    new EnumItem("Colomiers"),
                    new EnumItem("Besançon"),
                    new EnumItem("Strasbourg"),
                    new EnumItem("Souillac"),
                ]),

                new EnumItem("italy", "Italie", [
                    new EnumItem("Rome"),
                    new EnumItem("Venise"),
                    new EnumItem("Naple"),
                    new EnumItem("Florence")
                ])
            ]
        );

        $items[] = new EnumItem(
            "africa",
            "Afrique",
            [
                new EnumItem("morroco", "Maroc", [
                    new EnumItem("Marrakech"),
                    new EnumItem("Casablanca"),
                    new EnumItem("Rabat"),
                    new EnumItem("Fès")
                ])
            ]
        );

        return $items;
    }
}
