# Jeu de données pour le développement

4 Smarts Structures :
1.  `DEVPERSON` : Identification de personne (ce n'est pas un compte)
2.  `DEVCLIENT` : Identification d'un client
3.  `DEVNOTE` : Note rédigée avec auteur et co-auteur
4.  `DEVBILL` : Facture avec bénéficaire et client

Les données générées sont aléatoires.
Le script `recordDevData` regénère un jeu de données.

    ./ank.php --script=recordDevData --help
    Record Many Data
    Usage:
       Options:
        --person=<number of person to create>
        --client=<number of client to create>
        --note ( MIN-MAX of note associated to person) , default is '0-3'
        --bill ( MIN-MAX  bill associated to person and clients) , default is '0-3'
        --accounts=<number of accounts to create>, default is '0'

Ce script, s'il est lancé de nouveau, efface les données précédentes.

Exemple :

    ./ank.php --script=recordDevData --person=20 --client=10 --note=0-3 --bill=3-10