/* eslint-disable react/prop-types */
import React from "react";

import ReactMarkdown from "react-markdown";
import gfm from "remark-gfm";

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
          <div className="form-check-label markdown">
            <ReactMarkdown plugins={[gfm]} source={this.props.label} />
          </div>
        </label>
      </div>
    );
  }
}
