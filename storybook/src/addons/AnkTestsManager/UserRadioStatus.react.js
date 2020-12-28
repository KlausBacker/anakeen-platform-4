/* eslint-disable react/prop-types */
import React from "react";

export default class UserRadioStatusReact extends React.Component {
  constructor(props) {
    super(props);
    this.commentRef = React.createRef();
    this.state = {
      selected: "todo"
    };
  }
  select(status) {
    this.setState({ selected: status });
    this.props.onSelect({ status: status, comment: this.commentRef.current.value });
  }
  render() {
    return (
      <div className="user-form-check">
        <button
          className="todo"
          data-selected={this.state.selected === "todo" ? "true" : null}
          onClick={() => this.select("todo")}
        >
          Non réalisé
        </button>
        <button
          className="skip"
          data-selected={this.state.selected === "skip" ? "true" : null}
          onClick={() => this.select("skip")}
        >
          Non réalisable
        </button>
        <button
          className="ok"
          data-selected={this.state.selected === "ok" ? "true" : null}
          onClick={() => this.select("ok")}
        >
          Conforme
        </button>
        <button
          className="ko"
          data-selected={this.state.selected === "ko" ? "true" : null}
          onClick={() => this.select("ko")}
        >
          Non conforme
        </button>
        <textarea ref={this.commentRef} placeholder="Saisissez ici vos remarques" />
      </div>
    );
  }
}
