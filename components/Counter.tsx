import React from "react";
import { dataCounter } from "./Data";

interface Props {
  children: React.ReactNode;
  title?: React.ReactNode;
}

const CounterList = (props: Props) => {
  const { children, title } = props;

  return (
    <li className="text-center">
      <p className="text-7xl font-semibold">{children}</p>
      {title && (
        <p
          className="text-xl 
        text-[#54595f] mt-1
        // text-decoration-color: #54595f
        "
        >
          {title}
        </p>
      )}
    </li>
  );
};

const Counter: React.FC = () => {
  return (
    <div>
      <ul className="flex justify-evenly">
        {dataCounter.map((item) => (
          <CounterList key={item.number} title={item.title}>
            {item.number}
          </CounterList>
        ))}
      </ul>
    </div>
  );
};

export default Counter;
