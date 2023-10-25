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

const dataHero = [
  {
    text: "One of the most affordable contractors in the UK",
    icon: <BiPound size="24px" className="mr-1" />,
  },
  {
    text: "Positive feedback from previous clients",
    icon: <FaRegThumbsUp size="24px" className="mr-1" />,
  },
  {
    text: "Available client references for your peace of mind",
    icon: <FiCheckSquare size="24px" className="mr-1" />,
  },
  {
    text: "We work to client`s deadlines and budgets strictly",
    icon: <FaClipboardList size="24px" className="mr-1" />,
  },
  {
    text: "Every project is quality supervised",
    icon: <FaUsersCog size="24px" className="mr-1" />,
  },
  {
    text: "Government approved credentials",
    icon: <FaCheck size="24px" className="mr-1" />,
  },
  {
    text: "Live Chat support to give you 24/7 live help",
    icon: <FaHeadset size="24px" className="mr-1" />,
  },
];

const dataCounter = [
  {
    number: "98%",
    title: "Quality",
  },
  {
    number: "847",
    title: "Clients to Date",
  },
  {
    number: "805",
    title: "Projects in 2022",
  },
  {
    number: "2,439",
    title: "Approved Subcontractors",
  },
];

export { dataHero, dataCounter };
