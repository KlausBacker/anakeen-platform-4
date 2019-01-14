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
    },

    nav: [
      { text: 'Accueil', link: '/' },
      {
        text: 'Guide', items: [
          { link: '/guide/smartData/', text: 'Smart Data' },
          { link: '/guide/userInterfaces/', text: 'Interfaces utilisateur' },
          { link: '/guide/security/', text: 'Sécurité' },
          { link: '/guide/workflow/', text: 'Workflow' },
          { link: '/guide/routes/', text: 'Routes' },
          { link: '/guide/localisation/', text: 'Traductions' },
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