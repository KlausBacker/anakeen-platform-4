module.exports = {
  title: "Anakeen Hub Station",
  description: "Guide de référence",
  evergreen: true,
  plugins: [
    '@vuepress/pwa',
    '@vuepress/back-to-top',
    ['@vuepress/search', {
      searchMaxSuggestions: 10
    }]
  ],
  base: process.env.BASEHREF || '/',
  head: [
    ['link', { rel: 'icon', href: 'favico.png', type: 'image/png' }]
  ],
  themeConfig: {
    displayAllHeaders: true, // Default: false
    serviceWorker: {
      updatePopup: true, // for worker cache
    },
    search: true,
    sidebar: {
      '/guide/configuration/': getConfigSideBar(),
      // '/guide/using/': getUsingSideBar(),
    },

    nav: [
      { text: 'Accueil', link: '/' },
      {
        text: 'Guide', items: [
          { link: '/guide/configuration/', text: 'Configuration' },
          { link: '/guide/using/', text: 'Utilisation' }
        ]
      },
      { text: 'Anakeen', link: 'https://www.anakeen.com/' },
    ]
  },
  markdown: {
    // options for markdown-it-anchor
    anchor: { permalink: true }, // options for markdown-it-toc
    toc: { includeLevel: [1, 2] },
    extendMarkdown: md =>
    {
      // use more markdown-it plugins!
      md.use(require('markdown-it-include'))
    }
  }
}

function getConfigSideBar() {
  return [
    {
      title: "Créer un Hub Station",
      children: [
        ['instanciation/instanciation.md', "Instancier un Hub Station"]
      ]
    },
    {
      title: "Configuration du Hub Station: côté client",
      children: [
        ['client/hubStationComponent.md', "Hub Station"],
        ['client/hubElementComponent.md', "Hub Element"],
        ['client/vueComponent.md', "Définir une entrée du Hub"]
      ]
    },
    {
      title: "Configuration du Hub Station: côté serveur",
      children: [
        ['server/hubConfigurationStructure.md', "Hub Structures"]
      ]
    }
  ];
}