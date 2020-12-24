import Chai from "chai";
const expect = Chai.expect;

export async function testGetValue(testArgs) {
  const controller = testArgs.controller ? testArgs.controller : null;

  expect(controller).to.not.equal(null);

  const smartFieldId = testArgs.fieldId;
  const expectedValue = testArgs.expected;
  const value = controller.getValue(smartFieldId);

  expect(value, "Value must exist and not be empty").to.not.equal(null);
  expect(value.value).to.equal(expectedValue);
}

export async function testGetValues(testArgs) {
  const controller = testArgs.controller ? testArgs.controller : null;

  expect(controller).to.not.equal(null);

  const smartFieldId = testArgs.fieldId;
  const expectedValue = testArgs.expected;
  const value = controller.getValue(smartFieldId);

  expect(value, "Value must exist and not be empty").to.not.equal(null);
  expect(value.value).to.equal(expectedValue);
}
