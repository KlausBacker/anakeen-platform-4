<?php

namespace Anakeen\Dev;

use Anakeen\Search\Internal\SearchSmartData;
use SmartStructure\Fields\Devnote as DevNoteFields;

class NoteData
{
    public function __invoke($minNote = 0, $maxNote = 3)
    {
        \Anakeen\Core\DbManager::query(sprintf("delete from family.%s", strtolower(\SmartStructure\Devnote::familyName)));

        $s = new SearchSmartData("", \SmartStructure\Devperson::familyName);

        $s->returnsOnly(["initid"]);
        $allPersons = $s->search();
        $count = count($allPersons);
        $c = 1;
        $cnote = 1;
        $allPersons = array_values($allPersons);
        $nc = 1;
        foreach ($allPersons as $person) {
            $max = rand($minNote, $maxNote);
            for ($i = 0; $i < $max; $i++) {
                $note = \Anakeen\Core\SEManager::createDocument(\SmartStructure\Devnote::familyName);
                $note->setValue(DevNoteFields::note_author, $person["initid"]);
                $note->setValue(DevNoteFields::note_title, sprintf("Note %04d", $nc++));
                $note->setValue(DevNoteFields::note_content, sprintf('<h1>Content for %04d </h1><p>Writed by <i>%s</i></p>', $c, \DocTitle::getTitle($person["initid"])));
                $note->setValue(DevNoteFields::note_location, \Anakeen\Dev\Data::getRandomTown());
                $maxco = rand(0, 3);
                if ($maxco) {
                    $coids = $cophones = [];
                    for ($coidx = 0; $coidx < $maxco; $coidx++) {
                        $subCoIds = [];
                        $maxSubCo = rand(0, 3);
                        for($subCo = 0; $subCo < $maxSubCo; $subCo++) {
                            $r = rand(0, $count - 1);
                            $subCoIds[] = $allPersons[$r]["initid"];
                        }
                        $coids[] = $subCoIds;
                        $cophones[] = sprintf("%02d %02d %02d %02d %02d", rand(1, 7), rand(0, 99), rand(0, 99), rand(0, 99), rand(0, 99));
                    }
                    $note->setValue(DevNoteFields::note_coauthor, $coids);
                    $note->setValue(DevNoteFields::note_phone, $cophones);
                    $note->setValue(DevNoteFields::note_redactdate, \Anakeen\Dev\Data::getRandomDate(1990, 2017));
                }
                $note->store();
                printf("\r%05d/%05d - %05d", $c, $count, $cnote++);
            }
            $c++;
        }
        print "\n";
    }
}