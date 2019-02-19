module.exports = config => {
  const entries = {};
  Object.keys(config.entry).forEach(key => {
    entries[`${key}.umd`] = config.entry[key];
  });
  return {
    entry: entries,
    output: {
      libraryTarget: "umd"
    }
  }
};
