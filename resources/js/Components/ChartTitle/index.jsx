export default function ChartTitle({ text, className = '' }) {
  return (
    <div className={`bg-ticksift-black w-full text-center p-3 ${className}`}>
      <h2 className="text-3xl font-bold uppercase text-slate-50">{text}</h2>
    </div>
  );
}
