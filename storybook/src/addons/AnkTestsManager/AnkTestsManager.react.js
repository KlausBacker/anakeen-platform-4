/* eslint-disable react/prop-types */
import React, { Component } from "react";
import Loader from "react-loader-spinner";
import emoji from "emoji-dictionary";
import uniqid from "uniqid";

import ReactMarkdown from "react-markdown";
import { listItem } from "react-markdown/lib/renderers";
import gfm from "remark-gfm";

import "./AnkTests.css";
import UserRadioStatusReact from "./UserRadioStatus.react";

import recordState from "./RecordState";

class StoryTests extends Component {
  constructor(props) {
    super(props);
    this.state = {};

    this.props.channel.on("displayTestResults", () => {
      recordState.displayOnSidebar();
    });
  }

  handleUserRadioChange(changeEvent, name) {
    this.setState(prevState => ({
      ...prevState,
      [name]: {
        status: changeEvent.status,
        testMessage: changeEvent.comment
      }
    }));
  }

  isTestRunning(option) {
    return !!this.state[option] && this.state[option].running;
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
        return "Réussi";
      } else {
        return "Échec";
      }
    }
    return "";
  }

  returnResult(testsName) {
    if (this.state && this.state[testsName] && this.state[testsName].testMessage) {
      return this.state[testsName].testMessage;
    }
    return "";
  }

  createAutoTask(option) {
    return (
      <tr key={option.testId} className="test-row">
        {!option.humanTasks || option.humanTasks.length === 0 ? (
          <td className="user-task-id">
            <div>{option.testId}</div>
          </td>
        ) : null}
        <td>{option.title}</td>
        <td className="status">
          <Loader visible={this.isTestRunning(option.testId)} type="TailSpin" color="#00BFFF" height={20} width={20} />
          <span className={this.isTest(option.testId)}>{this.writeStatus(option.testId)}</span>
        </td>
        <td className="status-message">{this.returnResult(option.testId)}</td>
      </tr>
    );
  }

  createAllTests() {
    const tests = this.props.autoTests;
    const reactRows = [];

    tests.forEach(currentValue => {
      reactRows.push(this.createHumanTasks(currentValue));
      reactRows.push(this.createAutoTask(currentValue));
    });
    return reactRows;
  }

  createHumanTasks(testData) {
    const humanTasks = testData.humanTasks;
    if (humanTasks && humanTasks.length > 0) {
      const humanTaskElements = humanTasks.map((currentValue, index) => {
        const idx = "I" + index.toString();
        return (
          <div key={idx} className="markdown human-task-description">
            <ReactMarkdown plugins={[gfm]} source={currentValue} />
          </div>
        );
      });

      return (
        <tr key={"HT" + testData.testId} className="human-task">
          <td className="user-task-id" rowSpan="2">
            <div>{testData.testId}</div>
          </td>
          <td className="human-task-description" colSpan="3">
            {humanTaskElements}
          </td>
        </tr>
      );
    }
    return null;
  }

  createUserVerifications(userTasks) {
    if (userTasks && userTasks.length > 0) {
      return userTasks.map((userTest, index) => {
        const idx = "I" + index.toString();
        return [
          <tr key={"UT" + userTest.testId} className="user-task">
            <td className="user-task-id" rowSpan="2">
              <div>{userTest.testId}</div>
            </td>
            <td className="user-task-description">
              <div key={idx} className="markdown user-task-description">
                <ReactMarkdown plugins={[gfm]} source={userTest.description} />
              </div>
            </td>
          </tr>,
          <tr key={"UTR" + userTest.testId} className="user-task-results">
            <td>
              <UserRadioStatusReact onSelect={event => this.handleUserRadioChange(event, userTest.testId)} />
            </td>
          </tr>
        ];
      });
    }
    return null;
  }

  isTestActive(testsName) {
    return this.state[testsName] === undefined || this.state[testsName].checked === true;
  }

  callTest(testToExecute) {
    this.setState(prevState => ({
      ...prevState,
      [testToExecute.testId]: {
        checked: true,
        running: true,
        status: null,
        testMessage: null
      }
    }));

    if (testToExecute.testCallback) {
      return new Promise((resolve, reject) => {
        const eventId = uniqid("ev");
        this.props.channel.emit("executeCallbackTest", testToExecute.testId, eventId);

        this.props.channel.once(eventId, info => {
          if (info.success) {
            return resolve(info);
          } else {
            return reject(info);
          }
        });
      })
        .then(info => {
          this.setState(prevState => ({
            ...prevState,
            [testToExecute.testId]: {
              checked: true,
              running: false,
              status: "statusSuccess",
              testMessage: info.message
            }
          }));
          return { testId: testToExecute.testId, success: true, message: info.message };
        })
        .catch(error => {
          this.setState(prevState => ({
            ...prevState,
            [testToExecute.testId]: {
              checked: true,
              running: false,
              status: "statusFail",
              testMessage: error.message
            }
          }));
          return { testId: testToExecute.testId, success: false, message: error.message };
        });
    } else {
      return Promise.resolve();
    }
  }

  callTests(testsToExecute) {
    const promises = testsToExecute.map(test => {
      return this.callTest(test);
    });
    return Promise.all(promises);
  }

  executeTestsNow() {
    const testsToExecute = this.props.autoTests.filter(currentValue => {
      return this.isTestActive(currentValue.testId);
    });
    this.callTests(testsToExecute).then(hop => {
      recordState.setKey(this.props.story.storyId, hop);
      recordState.displayOnSidebar();
    });
  }
  recordResults() {
    window.alert("Pas encore fait. Désolé. 🙄");
  }

  render() {
    const emojiSupport = text => text.value.replace(/:\w+:/gi, name => emoji.getUnicode(name));

    recordState.displayOnSidebar();

    const renderers = {
      text: emojiSupport,
      listItem: props => {
        const liElement = listItem(props);
        // add custom class to hide dot of ul html element
        if (props.checked !== null && props.checked !== undefined) {
          return React.cloneElement(liElement, {
            className: "task-list-item"
          });
        }

        return liElement;
      }
    };

    return (
      <div className="ank-tests">
        {!this.props.readme && this.props.userTests.length === 0 && this.props.autoTests.length === 0 ? (
          <div className="markdown no-test">
            {" "}
            <ReactMarkdown
              renderers={renderers}
              plugins={[gfm]}
              source="# Pas de tests pour cette histoire :confused:"
            />
          </div>
        ) : null}

        {this.props.readme ? (
          <div className="markdown readme">
            {" "}
            <ReactMarkdown renderers={renderers} plugins={[gfm]} source={this.props.readme} />
          </div>
        ) : null}

        {this.props.userTests.length ? (
          <table className="user-tests">
            <thead>
              <tr>
                <th colSpan="2">Tests manuels</th>
              </tr>
            </thead>
            <tbody>{this.createUserVerifications(this.props.userTests)}</tbody>
          </table>
        ) : null}

        {this.props.autoTests.length ? (
          <table className="semiauto-tests">
            <thead>
              <tr>
                <th colSpan="4">Tests semi-automatiques</th>
              </tr>
            </thead>
            <tbody>
              <tr className="test-row">
                <td colSpan="4">
                  <button className="primary" onClick={evt => this.executeTestsNow(evt)}>
                    Lancer les {this.props.autoTests.length} tests
                  </button>
                </td>
              </tr>
              {this.createAllTests()}
            </tbody>
          </table>
        ) : null}

        {this.props.autoTests.length + this.props.userTests.length > 0 ? (
          <div className="results-sending">
            <button className="primary results-send-action" onClick={evt => this.recordResults(evt)}>
              Exporter les résultats
            </button>
          </div>
        ) : null}
      </div>
    );
  }
}

export default StoryTests;
