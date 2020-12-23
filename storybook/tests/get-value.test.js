function testGetValue(testArgs) {
  const controller = testArgs.controller.uid ? testArgs.controller : null;
  return new Promise((resolve, reject) => {
    if (controller) {
      const smartFieldId = testArgs.fieldId;
      const expectedValue = testArgs.expected;
      const value = controller.getValue(smartFieldId);
      if (value && value.value === expectedValue) {
        resolve();
      }
    } else {
      reject(new Error("The controller does not exist"));
    }
  });
}

export default testGetValue;