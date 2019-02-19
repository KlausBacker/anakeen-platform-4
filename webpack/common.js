module.exports = config => {
  const entries = {};
  Object.keys(config.entry).forEach(key => {
    entries[`${key}.common`] = config.entry[key];
  });
  return {
    entry: entries,
    output: {
      libraryTarget: "commonjs"
    }
  }
};
