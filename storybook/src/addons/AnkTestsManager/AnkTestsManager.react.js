// eslint-disable-next-line no-unused-vars
import React, { Component } from "react";
import TestsFunctions from "../../../tests";
import Checkbox from "./Checkbox.react";
import Loader from "react-loader-spinner";
import "./AnkTests.css";

class StoryTests extends Component {
  constructor(props) {
    super(props);
    this.state = {};
  }

  selectAllCheckboxes(isSelected) {
    this.setState(
      this.props.tests.reduce((nextState, currentValue) => {
        nextState[currentValue.testId] = {
          checked: isSelected,
          running: false,
          status: null,
          errorMsg: null
        };
        return nextState;
      }, {})
    );
  }

  handleCheckboxChange(changeEvent) {
    const { name, checked } = changeEvent.target;
    this.setState(prevState => ({
      ...prevState,
      [name]: {
        checked,
        running: false,
        status: null,
        errorMsg: null
      }
    }));
  }

  isTestRunning(option) {
    return !!this.state[option] && this.state[option].running;
  }

  getTestedController() {
    const iframeWindow = window.length ? window[0] : null;
    if (iframeWindow) {
      const controllers = iframeWindow.ank.smartElement.globalController.getControllers();
      // TODO: find a way to correctly retrieve the good controller
      const finded = controllers.filter(c => !!c._tested_);
      return finded[finded.length - 1];
    }
    return null;
  }

  isTestFail(testsName) {
    if (this.state && this.state[testsName] && this.state[testsName].status) {
      if (this.state[testsName].status === "statusSuccess") {
        return false;
      } else {
        return true;
      }
    }
    return false;
  }

  isTest(testsName) {
    if (this.state && this.state[testsName] && this.state[testsName].status) {
      return this.state[testsName].status;
    }
    return "";
  }

  writeStatus(testsName) {
    if (this.state && this.state[testsName] && this.state[testsName].status) {
      if (this.state[testsName].status === "statusSuccess") {
        return "Success";
      } else {
        return "Fail";
      }
    }
    return "";
  }

  returnResult(testsName) {
    if (this.state && this.state[testsName] && this.state[testsName].errorMsg) {
        return this.state[testsName].errorMsg;
    }
    return "";
  }

  createCheckbox(option) {
    // let expectedTemplate = null;
    let resultTemplate = null;
    // if (this.isTestFail(option.testId)) {
    //   expectedTemplate = (
    //     <td>
    //       <span className="expected">Expected: </span>
    //       <span>"{option.expected}"</span>
    //     </td>
    //   );
    // }
    if (this.isTestFail(option.testId)) {
      resultTemplate = <td>{this.returnResult(option.testId)}</td>;
    }
    return (
      <tr key={option.testId} className="betweenTr">
        <td>
          <Checkbox
            label={option.title}
            name={option.testId}
            isSelected={this.isTestActive(option.testId)}
            onCheckboxChange={event => this.handleCheckboxChange(event)}
            key={option.testId}
          />
        </td>
        <td>
          <Loader visible={this.isTestRunning(option.testId)} type="TailSpin" color="#00BFFF" height={20} width={20} />
        </td>
        <td>
          <span className={this.isTest(option.testId)}>{this.writeStatus(option.testId)}</span>
        </td>
       {/* {expectedTemplate} */}
        {resultTemplate}
      </tr>
    );
  }

  createCheckboxes() {
    const tests = this.props.tests;
    return tests.map(currentValue => {
      return this.createCheckbox(currentValue);
    });
  }

  isTestActive(testsName) {
    return this.state[testsName] === undefined || this.state[testsName].checked === true;
  }

  checkCheckBox() {
    return this.props.tests.reduce((isChecked, currentValue) => {
      isChecked = isChecked && this.isTestActive(currentValue.testId);
      return isChecked;
    }, true);
  }

  callTest(testToExecute) {
    this.setState(prevState => ({
      ...prevState,
      [testToExecute.testId]: {
        checked: true,
        running: true,
        status: null,
        errorMsg: null
      }
    }));
    const controller = this.getTestedController(testToExecute.testId);
    return TestsFunctions[testToExecute.testId]({ ...testToExecute, controller })
      .then(() => {
        this.setState(prevState => ({
          ...prevState,
          [testToExecute.testId]: {
            checked: true,
            running: false,
            status: "statusSuccess",
            errorMsg: null
          }
        }));
        console.log("Test succeed !");
      })
      .catch(error => {
        this.setState(prevState => ({
          ...prevState,
          [testToExecute.testId]: {
            checked: true,
            running: false,
            status: "statusFail",
            errorMsg: error.message
          }
        }));
        console.error("Test failed: ", error.message);
      });
  }

  callTests(testsToExecute) {
    const promises = testsToExecute.map(test => {
      return this.callTest(test);
    });
    return Promise.all(promises);
  }

  clickBtn() {
    const testsToExecute = this.props.tests.filter(currentValue => {
      return this.isTestActive(currentValue.testId);
    });
    this.callTests(testsToExecute);
  }

  render() {
    return (
      <div>
        {this.props.tests.length ? (
          <ol>
            <table>
              <thead>
                <tr>
                  <th colSpan="2">Tout les tests de la storie</th>
                </tr>
              </thead>
              <tbody>
                <tr className="betweenTr">
                  <td colSpan="2">
                    <Checkbox
                      label="Sélectionner tout les tests"
                      name="SelectAllTests"
                      isSelected={this.checkCheckBox()}
                      onCheckboxChange={evt => this.selectAllCheckboxes(evt.target.checked)}
                      key="SelectAllTests"
                    />
                  </td>
                  <td colSpan="3">
                    <button onClick={evt => this.clickBtn(evt)}>Lancer les tests</button>
                  </td>
                </tr>
                {this.createCheckboxes()}
              </tbody>
            </table>
          </ol>
        ) : null}
      </div>
    );
  }
}

export default StoryTests;
