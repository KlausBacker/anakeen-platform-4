module.exports = config => {
  const entries = {};
  Object.keys(config.entry).forEach(key => {
    entries[`${key}`] = config.entry[key];
  });
  return {
    entry: entries,
  }
};
