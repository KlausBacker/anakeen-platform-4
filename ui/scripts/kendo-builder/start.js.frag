(function (name, factory) {
  console.log("NAME = " + name);
  try {
      // NodeJS
      if (module !== undefined && module.exports !== undefined) {
        module.id = name;
        module.exports = factory();
      }

      // CommonJS
      else if (module !== undefined && exports !== undefined) {
        module.id = name;
        exports = factory();
      }
  } catch (err) {
    if (typeof define === 'function' && define.amd) {
        define(name, factory);
    }
    else if (window) {
        // On definie `require` pour gérer les
        // dépendances de la même façon que les autres
        window.require = window.require ||
        function (module) { return window[module]; };

        window[name] = factory();
    }
  }
}('kendo', function () {