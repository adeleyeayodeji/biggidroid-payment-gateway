import { decodeEntities } from "@wordpress/html-entities";

const { registerPaymentMethod } = window.wc.wcBlocksRegistry;
const { getSetting } = window.wc.wcSettings;

const settings = getSetting("biggidroid_payment_data", {});

const label = decodeEntities(settings.title);

const Content = () => {
  return decodeEntities(settings.description || "");
};

const Label = (props) => {
  return (
    <div
      style={{
        display: "flex",
        flexDirection: "row",
        gap: "0.5rem",
        justifyContent: "center",
        alignItems: "center"
      }}>
      <div>{decodeEntities(settings.title)}</div>
      <img
        style={{ height: 10 }}
        src={`${settings.logo_urls}`}
        alt={decodeEntities(settings.title)}
      />
    </div>
  );
};

registerPaymentMethod({
  name: "biggidroid_payment",
  label: <Label />,
  content: <Content />,
  edit: <Content />,
  canMakePayment: () => true,
  ariaLabel: label,
  supports: {
    features: settings.supports
  }
});
