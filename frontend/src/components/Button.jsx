import { useNavigate } from "react-router-dom";

export default function Button() {
  const navigate = useNavigate();

  return (
    <button
      onClick={() => navigate("/events")}
      className="text-2xl font-semibold my-5 bg-[#0062FF] hover:bg-[#21497e] h-16 w-64 rounded-full"
    >
      Explorer nos JPO
    </button>
  );
}
