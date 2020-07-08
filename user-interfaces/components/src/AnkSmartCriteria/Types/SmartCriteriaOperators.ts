export default class SmartCriteriaOperator {
  buildMap() {
    return {
      isEmpty: {
        options: {
          not: 1
        }
      },
      oneContains: {
        options: {
          not: 1,
          all: 2
        }
      }
    };
  }
}
