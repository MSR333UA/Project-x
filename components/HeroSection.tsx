import React from "react";
import { dataHero } from "./Data";

interface Props {
  children: React.ReactNode;
  icon?: React.ReactNode;
}

const NavLists = (props: Props) => {
  const { children, icon } = props;
  return (
    <li className="mb-3">
      <span className="flex items-center">
        {icon}
        {children}
      </span>
    </li>
  );
};

const HeroSection: React.FC = () => {
  return (
    <div className="relative w-full  ">
      <video
        className="object-cover w-full h-[450px] "
        src="../assets/video_Sales.mp4"
        autoPlay
        loop
        muted
      />

      <div className="absolute top-[50px] left-20  flex flex-col justify-center  ">
        <h1 className="text-3xl mb-5 font-semibold text-[#FAFAFB]">
          Why Choose British Builders?
        </h1>
        <ul className="text-[#FAFAFB] text-xl">
          {dataHero.map((item) => (
            <NavLists key={item.text} icon={item.icon}>
              {item.text}
            </NavLists>
          ))}
        </ul>
      </div>
      <div>
        {/* <ul>
          <li></li>
        </ul> */}
      </div>
    </div>
  );
};

export default HeroSection;
