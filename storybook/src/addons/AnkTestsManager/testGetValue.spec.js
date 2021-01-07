import Chai from "chai";
const expect = Chai.expect;

export async function smartElementGetRawValue(testArgs) {
  const controller = testArgs.controller ? testArgs.controller : null;

  expect(controller).to.not.equal(null);

  const smartFieldId = testArgs.fieldId;
  const expectedValue = testArgs.expected;
  const value = controller.getValue(smartFieldId);

  const fieldInfo = controller.getSmartField(smartFieldId);

  expect(fieldInfo, `Le champ "${smartFieldId}" n'a pas été trouvé`).to.not.equal(null);

  expect(value, `Le champ "${fieldInfo.getLabel()}" est vide`).to.not.equal(null);
  expect(
    value.value,
    `La valeur du champ "${fieldInfo.getLabel()}" doit être "${expectedValue}".\nPour l'instant sa valeur est "${
      value.value
    }"`
  ).to.equal(expectedValue);
  return `La valeur du champ "${fieldInfo.getLabel()}" est "${value.value}"`;
}

export async function smartElementGetRawValues(testArgs) {
  const controller = testArgs.controller ? testArgs.controller : null;

  expect(controller).to.not.equal(null);

  const smartFieldId = testArgs.fieldId;
  const expectedValue = testArgs.expected;

  const fieldInfo = controller.getSmartField(smartFieldId);

  expect(fieldInfo, `Le champ "${smartFieldId}" n'a pas été trouvé`).to.not.equal(null);

  const value = controller.getValue(smartFieldId);
  const rawValues = value.map(item => item.value);

  expect(value, "Value must exists and not be empty").to.not.equal(null);
  expect(
    rawValues,
    `La valeur du champ "${fieldInfo.getLabel()}" doit être "${expectedValue.toString()}".\nPour l'instant sa valeur est "${rawValues.toString()}"`
  ).to.eql(expectedValue);
  return `Les valeurs du champ "${fieldInfo.getLabel()}" sont "${rawValues.toString()}"`;
}
