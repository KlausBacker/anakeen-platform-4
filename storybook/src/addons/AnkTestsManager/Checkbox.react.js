import React from "react";

export default class Checkbox extends React.Component {
  render() {
    return (
      <div className="form-check">
        <label>
          <input
            type="checkbox"
            name={this.props.name}
            checked={this.props.isSelected}
            onChange={this.props.onCheckboxChange}
            className="form-check-input"
          />
          {this.props.label}
        </label>
      </div>
    );
  }
}
