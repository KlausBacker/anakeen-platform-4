var describe= function(description, specDefinitions) {
    return env.describe(description, specDefinitions);
};

var xdescribe= function(description, specDefinitions) {
    return env.xdescribe(description, specDefinitions);
};

var fdescribe= function(description, specDefinitions) {
    return env.fdescribe(description, specDefinitions);
};

var it= function() {
    return env.it.apply(env, arguments);
};

var xit= function() {
    return env.xit.apply(env, arguments);
};

var fit= function() {
    return env.fit.apply(env, arguments);
};

var beforeEach= function() {
    return env.beforeEach.apply(env, arguments);
};

var afterEach= function() {
    return env.afterEach.apply(env, arguments);
};

var beforeAll= function() {
    return env.beforeAll.apply(env, arguments);
};

var afterAll= function() {
    return env.afterAll.apply(env, arguments);
};

var expect= function(actual) {
    return env.expect(actual);
};

var pending= function() {
    return env.pending.apply(env, arguments);
};

var fail= function() {
    return env.fail.apply(env, arguments);
};

var spyOn= function(obj, methodName) {
    return env.spyOn(obj, methodName);
};