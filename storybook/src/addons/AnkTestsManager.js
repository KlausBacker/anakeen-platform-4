import React, { Component, Fragment } from "react";
import TestsFunctions from "../../tests";
import Checkbox from "./Checkbox";
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
    console.log(name);
    this.setState(prevState => ({
      ...prevState,
      [name]: {
        checked,
        running: false
      }
    }));
  }

  isTestRunning(option) {
    console.log(this.state[option]);
    return !!this.state[option] && this.state[option].running;
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
    console.log(testToExecute);
    return TestsFunctions[testToExecute.jest](testToExecute)
      .then(() => {
        this.setState(prevState => ({
          ...prevState,
          [testToExecute.title]: {
            checked: true,
            running: false
          }
        }));
      })
      .catch(error => {
        console.error(error.message);
      });
  }

  callTests(testsToExecute) {
    const promises = testsToExecute.map(test => {
      return this.callTest(test);
    });
    return Promise.all(promises);
  }

  clickBtn(evt) {
    const testsToExecute = this.props.tests.filter(currentValue => {
      return this.isTestActive(currentValue.title);
    });
    this.callTests(testsToExecute);
  }

  render() {
    return (
      <Fragment>
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
      </Fragment>
    );
  }
}

export default StoryTests;
