import React from "react";
import { BiPound } from "react-icons/bi";
import {
  FaRegThumbsUp,
  FaClipboardList,
  FaUsersCog,
  FaCheck,
  FaHeadset,
} from "react-icons/fa";
import { FiCheckSquare } from "react-icons/fi";

interface Props {
  children: React.ReactNode;
  icon?: React.ReactNode;
}
const data = [
  {
    text: "One of the most affordable contractors in the UK",
    icon: <BiPound size="24px" className="mr-0.5" />,
  },
  { text: "Positive feedback from previous clients", icon: <FaRegThumbsUp /> },
  {
    text: "Available client references for your peace of mind",
    icon: <FiCheckSquare />,
  },
  {
    text: "We work to client`s deadlines and budgets strictly",
    icon: <FaClipboardList />,
  },
  { text: "Every project is quality supervised", icon: <FaUsersCog /> },
  { text: "Government approved credentials", icon: <FaCheck /> },
  {
    text: "Live Chat support to give you 24/7 live help",
    icon: <FaHeadset />,
  },
  { number: "98%", title: "Quality" },
];

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
    <div className="relative w-full h-screen ">
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
          {data.map((item, index) => (
            <NavLists key={index} icon={item.icon}>
              {item.text}
            </NavLists>
          ))}
        </ul>
      </div>
      <div>
        <ul>
          <li></li>
        </ul>
      </div>
    </div>
  );
};

export default HeroSection;
