// eslint-disable-next-line no-unused-vars
import React, { Component } from "react";
import TestsFunctions from "../../../tests";
import Checkbox from "./Checkbox.react";
import Loader from "react-loader-spinner";

class StoryTests extends Component {
  constructor(props) {
    super(props);
    this.state = {};
  }

  selectAllCheckboxes(isSelected) {
    this.setState(
      this.props.tests.reduce((nextState, currentValue) => {
        nextState[currentValue.title] = {
          checked: isSelected,
          running: false
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
        running: false
      }
    }));
  }

  isTestRunning(option) {
    return !!this.state[option] && this.state[option].running;
  }

  getTestedController(testId) {
    const iframeWindow = window.length ? window[0] : null;
    if (iframeWindow) {
      const controllers = iframeWindow.ank.smartElement.globalController.getControllers();
      // TODO: find a way to correctly retrieve the good controller
      const finded = controllers.filter(c => c._tested_ === testId);
      return finded[finded.length - 1];
    }
    return null;
  }

  createCheckbox(option) {
    return (
      <tr key={option}>
        <td>
          <Checkbox
            label={option}
            isSelected={this.isTestActive(option)}
            onCheckboxChange={event => this.handleCheckboxChange(event)}
            key={option}
          />
        </td>
        <td>
          <Loader visible={this.isTestRunning(option)} type="TailSpin" color="#00BFFF" height={20} width={20} />
        </td>
      </tr>
    );
  }

  createCheckboxes() {
    const tests = this.props.tests;
    return tests.map(currentValue => {
      return this.createCheckbox(currentValue.title);
    });
  }

  isTestActive(testsName) {
    return this.state[testsName] === undefined || this.state[testsName].checked === true;
  }

  checkCheckBox() {
    return this.props.tests.reduce((isChecked, currentValue) => {
      isChecked = isChecked && this.isTestActive(currentValue.title);
      return isChecked;
    }, true);
  }

  callTest(testToExecute) {
    this.setState(prevState => ({
      ...prevState,
      [testToExecute.title]: {
        checked: true,
        running: true
      }
    }));
    const controller = this.getTestedController(testToExecute.jest);
    return TestsFunctions[testToExecute.jest]({ ...testToExecute, controller })
      .then(() => {
        this.setState(prevState => ({
          ...prevState,
          [testToExecute.title]: {
            checked: true,
            running: false
          }
        }));
        console.log("Test succeed !");
      })
      .catch(error => {
        this.setState(prevState => ({
          ...prevState,
          [testToExecute.title]: {
            checked: true,
            running: false
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
      return this.isTestActive(currentValue.title);
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
                <tr>
                  <td>
                    <Checkbox
                      label="Sélectionner tout les tests"
                      isSelected={this.checkCheckBox()}
                      onCheckboxChange={evt => this.selectAllCheckboxes(evt.target.checked)}
                      key="AllTests"
                    />
                  </td>
                  <td>
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
