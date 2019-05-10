const appUrl = process.env.APP_URL;
const appRoot = process.env.APP_ROOT;
const discoveryCore = process.env.DISCOVERY_CORE;
const ga = process.env.GA ? true : false;

module.exports = exports = {
  appName: 'Arabic Collections Online',
  appOGDescription: 'Arabic Collections Online is a publicly available digital library of public domain Arabic language content. Funded by New York University Abu Dhabi, this project aims to expose 23,000 volumes from NYU and partner institutions over a period of five years.',
  appOGImage: [
    {
      path: `${appUrl}/images/booklarge.png`
    }
  ],
  appOGUrl: appUrl,
  collectionCode: 'aco',
  appUrl: appUrl,
  appUrlx: appUrl,
  appRoot: appRoot,
  discovery: `${discoveryCore}/select/`,
  ga: ga
};
